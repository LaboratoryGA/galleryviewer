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
 * Templater component which retrieves the photos of a named gallery, and
 * jump passes it on to the templater engine to do whatever it will.
 *
 * @author fiveht
 */
final class GalleryviewerComponent extends TemplaterComponentTmpl {
	
	private static $DEFAULTS = array(
		'template' => 'galleryviewer/grid.html',
		'max' => 20
	);
	
	public function Show($attributes) {
		ClaApplication::Enter('galleryviewer');
		
		$options = array_merge(static::$DEFAULTS, is_array($attributes) ? $attributes : array());
		
		if (!key_exists('album_id', $options)) {
			return 'Missing "album_id"';
		}
		
		$args = $this->generateArgs($options['album_id'], $options['max']);
		
		return /*'<pre>' . print_r($args, true) . '</pre>' .*/ $this->CallTemplater($options['template'], $args);
	}
	
//	/**
//	 * This method comes to us courtest of Alexander. 
//	 */
//	private function generateArgs($albumID) {
//		$controller = new ImageListViewController();
//
//		ImageListViewController::DoTreeGPCOperations($controller, 'GALLERY_', $albumID);
//
//		$controller->SetSortParams('date_created', 'desc');
//
//		$provider = new ImageListProvider($controller);
//
////		$provider->SetCurrentCollections($collection_ids);
////
////		if (strlen($simple_keywords) > 0) {
////			$provider->SetSimpleSearch($simple_keywords);
////			$args['simple_keywords.value'] = $simple_keywords;
////		}
//
//		$list = $provider->GetTree($albumID);
//		$view = new ImageListView($controller);
//		$common_url_params = $provider->GetURLParams();
//
//		$view->SetURLParams("album_id=" . $albumID . $common_url_params);
//		$view->AttachClaTree($list);
//		$view->GetTreeHTML($args);
//		
//		// create some unique carousel identifier
//		$args['id.id'] = uniqid();
//		
//		foreach ($args['image_list_items.datasrc'] as $index => &$slide) {
//			// generate an associated navigation datasrc for each item
//			$args['navigation.datasrc'][] = array(
//				'nav_item.data-slide-to'	=> $index,
//				'nav_item.data-target'		=> "#{$args['id.id']}-{$slide['id.body']}",
//				'nav_item.+class'			=> $index === 0 ? 'active' : ''
//			);
//			
//			$slide['item.+class'] = $index === 0 ? 'active' : '';
//			$slide['item.id'] = "{$args['id.id']}-{$slide['id.body']}";
//			$slide['image.src'] = "../gallery/thumbnail.php?id={$slide['id.body']}&prefix=medium";
//		}
//		
//		$args['prev.href'] = "#{$args['id.id']}";
//		$args['next.href'] = "#{$args['id.id']}";
//		
//		return $args;
//	}
//	
	
	/**
	 * Constructs an associative array suitable for passing through to the
	 * templater engine
	 * 
	 * @param integer $albumID the unique ID number of the album
	 */
	private function generateArgs($albumID, $max) {
		$args = $this->generateBaseArgs($albumID);
		$list = $this->getImagesDOM($albumID, $max);
		
		foreach ($list->documentElement->childNodes as $index => $item) {
			$slide = ImageListNode::ToArray($item);
			// generate a datasrc entry for this slide
			$args['slides.datasrc'][] = $this->generateSlideData($args, $index, $slide);
			// generate an associated navigation datasrc for each item
			$args['navigation.datasrc'][] = $this->generateNavigationData($args, $index, $slide);
		}
		
		$args['prev.href'] = "#{$args['id.id']}";
		$args['next.href'] = "#{$args['id.id']}";

//		die('<pre>' . print_r($args, true));
		return $args;
	}
	
	/**
	 * Constructs the base args array, namely the unique HTML ID marker, and
	 * the two placeholders for the slides and navigation datasrc's
	 * 
	 * @param integer $albumID the unique ID number of the album
	 * @return array
	 */
	private function generateBaseArgs($albumID) {
		return array(
			'id.id' => uniqid() . '-' . $albumID,
			'slides.datasrc' => array(),
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

		ImageListViewController::DoTreeGPCOperations($controller, 'GALLERY_', $albumID);

//		$controller->SetMetadataListToFetch(array('copyrighted', 'color'));
		$controller->SetSortParams('date_created', 'desc');
		// if max is zero, force it to a boolean false
		$controller->SetPagingParams(!$max ? false : $max);
		
		$provider = new ImageListProvider($controller);
		
		return $provider->GetTree($albumID);
	}
	
	/**
	 * Constructs a single datasrc entry holding information about a single
	 * slide
	 * 
	 * @param array $args the current templater args
	 * @param integer $index the slide number
	 * @param array $slide associative array containing information about the
	 * image
	 * @return array the associative array for the templater
	 */
	private function generateSlideData(array $args, $index, array $slide) {
		return array(
			'image.src'		=> "../gallery/thumbnail.php?id={$slide['id']}&prefix=medium",
			'item.+class'	=> ($index === 0 ? 'active' : ''),
			'item.id'		=> "{$args['id.id']}-{$slide['id']}",
			'title.body'	=> $slide['title']
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
			'nav_item.data-target'		=> "#{$args['id.id']}-{$slide['id']}",
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