/**
 * editor_plugin_src.js
 *
 * Copyright 2013, maxweb Team
 * Released under LGPL License.
 *
 * License: http://www.maxweb.us/
 * Contributing: http://www.maxweb.us/
 */

(function($) {
    tinymce.create('tinymce.plugins.ForgivingHealsImageUploadPlugin', {
        init : function(ed, url) {

            ed.addCommand('forgivinghealsOpenModal', function() {
                if(typeof ForgivingHeals.Views.UploadImagesModal !== "undefined"){
                    var uploadIMGModal = new ForgivingHeals.Views.UploadImagesModal({ el:$("#upload_images") });
                    uploadIMGModal.openModal();
                }
            });

            ed.addButton('forgivinghealsimage', {
                title : forgivingheals_front.texts.upload_images,
                image : url + '/img/upload-image.gif',
                cmd : 'forgivinghealsOpenModal'
            });
        },
        getInfo : function() {
            return {
                longname : 'ForgivingHeals Images Upload',
                author : 'thaint',
                version : '1.0'
            };
        }
    });

    tinymce.PluginManager.add('forgivinghealsimage', tinymce.plugins.ForgivingHealsImageUploadPlugin);
})(jQuery);
