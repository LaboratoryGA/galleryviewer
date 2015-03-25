<?php

/*
 * Copyright (C) 2015 Nathan Crause - All rights reserved
 *
 * This file is part of Intranet_Labs
 *
 * Copying, modification, duplication in whole or in part without
 * the express written consent of the copyright holder is
 * expressly prohibited under the Berne Convention and the
 * Buenos Aires Convention.
 */

/**
 * Templater component which retrieves the photos of a named gallery album, and
 * passes it on to the templater engine to do whatever it will.
 * <p>
 * This component has the following attributes:
 * <ul>
 * <li>album_id - <strong>(required)</strong> this defines the unique ID number
 * of the gallery album to display</li>
 * <li>template - the template file which will be used to render the images</li>
 * <li>max - the maximum number of images to retrieve from the album</li>
 * <li>thumbnail_width - if the template uses thumbnails (possible for
 * lightboxes) this attribute defines the pixel width of the thumbnail</li>
 * <li>thumbnail_height - if the template uses thumbnails (possible for
 * lightboxes) this attribute defines the pixel height of the thumbnail</li>
 * <li>speed - if teh template is a carousel/slider, this defines the speed at
 * which the images rotate</li>
 * </ul>
 *
 * @author Nathan Crause
 */
final class GalleryviewerComponent extends TemplaterComponentTmpl {
	
	/**
	 * Required argument for defining the unique ID number of the album
	 */
	const OPT_ALBUM_ID = 'album_id';
	
	/**
	 * Defines the templater file which will render this
	 */
	const OPT_TEMPLATE = 'template';
	
	/**
	 * Defines the maximum number of images to retrieve
	 */
	const OPT_MAX = 'max';
	
	/**
	 * For templates which use a thumbnail, this is the width
	 */
	const OPT_THUMBNAIL_WIDTH = 'thumbnail_width';
	
	/**
	 * For templates which use a thumbnail, this is the height
	 */
	const OPT_THUMBNAIL_HEIGHT = 'thumbnail_height';
	
	/**
	 * For slider/carousel style templates, this defines the speed with which to
	 * rotate to the next image
	 */
	const OPT_SPEED = 'speed';
	
	private static $DEFAULTS = array(
		self::OPT_TEMPLATE => 'galleryviewer/slider.html',
		self::OPT_MAX => 20,
		self::OPT_THUMBNAIL_WIDTH => 300,
		self::OPT_THUMBNAIL_HEIGHT => 300,
		self::OPT_SPEED => 5000
	);
	
	public function Show($attributes) {
		ClaApplication::Enter('galleryviewer');
		
		$options = array_merge(static::$DEFAULTS, is_array($attributes) 
				? $attributes 
				: array());
		
		if (!key_exists(self::OPT_ALBUM_ID, $options)) {
			return 'Missing "album_id"';
		}
		
		$args = $this->generateArgs($options[self::OPT_ALBUM_ID], 
				$options[self::OPT_MAX], $options[self::OPT_THUMBNAIL_WIDTH],
				$options[self::OPT_THUMBNAIL_HEIGHT]);
		
		// final few touchips to the args
		$args['album.data-interval'] = $options[self::OPT_SPEED];
		
		return $this->CallTemplater($options['template'], $args);
	}
	
	/**
	 * Constructs an associative array suitable for passing through to the
	 * templater engine
	 * 
	 * @param integer $albumID the unique ID number of the album
	 */
	private function generateArgs($albumID, $max, $thumbnailWidth, 
			$thumbnailHeight) {
		
		$args = $this->generateBaseArgs($albumID);
		$list = $this->getImagesDOM($albumID, $max);
		
		foreach ($list->documentElement->childNodes as $index => $item) {
			$image = ImageListNode::ToArray($item);
			// generate a datasrc entry for this slide
			$args['images.datasrc'][] = $this->generateImageData($args, $index, 
					$image, $thumbnailWidth, $thumbnailHeight);
			// generate an associated navigation datasrc for each item
			$args['navigation.datasrc'][] = $this->generateNavigationData($args, 
					$index, $image);
		}
		
		$args['prev.href'] = "#{$args['album.id']}";
		$args['next.href'] = "#{$args['album.id']}";

//		die('<pre>' . print_r($args, true));
		return $args;
	}
	
	/**
	 * Constructs the base args array, namely the unique HTML ID marker, and
	 * the two placeholders for the images and navigation datasrc's
	 * 
	 * @param integer $albumID the unique ID number of the album
	 * @return array
	 */
	private function generateBaseArgs($albumID) {
		return array(
			'album.id' => uniqid() . '-' . $albumID,
			'images.datasrc' => array(),
			'navigation.datasrc' => array()
		);
	}
	
	/**
	 * Retrieves the images from the gallery album
	 * 
	 * @param integer $albumID the unique ID number of the album
	 * @param integer|boolean $max the maximum number of images to retrieve,
	 * or <code>false</code> if no limit
	 * @return DOMDocument
	 */
	private function getImagesDOM($albumID, $max) {
		$controller = new ImageListViewController();

//		ImageListViewController::DoTreeGPCOperations($controller, 'GALLERY_', 
//				$albumID);

//		$controller->SetMetadataListToFetch(array('copyrighted', 'color'));
		$controller->SetSortParams('date_created', 'desc');
		// if max is zero, force it to a boolean false
		$controller->SetPagingParams(!$max ? false : true, $max);
		
		$provider = new ImageListProvider($controller);
		
		return $provider->GetTree($albumID);
	}
	
	/**
	 * Constructs a single datasrc entry holding information about a single
	 * image
	 * 
	 * @param array $args the current templater args
	 * @param integer $index the image number
	 * @param array $image associative array containing information about the
	 * image
	 * @return array the associative array for the templater
	 */
	private function generateImageData(array $args, $index, array $image,
			$thumbnailWidth, $thumbnailHeight) {
		
		return array(
			'image.src'		=> 
					'../gallery/thumbnail.php?' . http_build_query(array(
						'id' => $image['id'],
						'prefix' => 'medium'
					)),
			'thumbnail.src'		=> 
					'../gallery/thumbnail.php?' . http_build_query(array(
						'id' => $image['id'],
						'prefix' => 'thumb',
						'c' => sprintf('%d_%d', $thumbnailWidth, $thumbnailHeight)
					)),
			'item.+class'	=> ($index === 0 ? 'active' : ''),
			'item.id'		=> "{$args['album.id']}-{$image['id']}",
			'title.body'	=> $image['title']
		);
	}
	
	/**
	 * Constructs a single datasrc entry holding information about a single
	 * navigation
	 * 
	 * @param array $args the current templater args
	 * @param integer $index the slide number
	 * @param array $slide associative array containing information about the
	 * navigation point
	 * @return array the associative array for the templater
	 */
	private function generateNavigationData(array $args, $index, array $slide) {
		return array(
			'nav_item.data-slide-to'	=> $index,
			'nav_item.data-target'		=> "#{$args['album.id']}-{$slide['id']}",
			'nav_item.+class'			=> ($index === 0 ? 'active' : '')
		);
	}
}

/*****
Sample output from "ImageListProvider::GetTree":
Array
(
    [0] => Array
        (
            [id] => 55
            [file_size] => 403911
            [title] => Dave NPR
            [album_id] => 9
            [date_created] => 20140923043014
            [keywords] => 
            [width] => 1200
            [height] => 1600
            [comments] => 0
            [permissions] => Array
                (
                )

            [creator] => Array
                (
                    [id] => 32
                    [firstname] => David
                    [surname] => Arril
                )

            [metadata] => Array
                (
                )

        )
 */