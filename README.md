#Galleryviewer
A Claromentis component providing a Bootstrap slideshow using a gallery album for images.

##Usage
###Component
As a component, you may embed into the home page or publish template using:
```html
<component class="GalleryviewerComponent" album_id="123" />
```

###IFRAME
This module provides no smart-object implementation. Instead, if you wish to
embed this item in a publish page, you may either use the 
[Shortcode](http://github/LaboratoryGA/shortcode) module, or embed it using
an IFRAME:
```html
<iframe src="/intranet/galleryviewer/iframe.php?album_id=123"></iframe>
```
