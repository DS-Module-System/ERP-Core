<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app_a' => [
        'path' => './assets/js/app.js',
        'entrypoint' => true,
    ],
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'tom-select' => [
        'version' => '2.3.1',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.2',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.2',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'blueimp-file-upload' => [
        'version' => '10.32.0',
    ],
    'decimal.js' => [
        'version' => '10.4.3',
    ],
    'flatpickr' => [
        'version' => '4.6.13',
    ],
    'flatpickr/dist/flatpickr.min.css' => [
        'version' => '4.6.13',
        'type' => 'css',
    ],
    'jquery.cookie' => [
        'version' => '1.4.1',
    ],
    'moment' => [
        'version' => '2.30.1',
    ],
    'tinymce' => [
        'version' => '6.8.3',
    ],
    'simplebar' => [
        'version' => '6.2.5',
    ],
    'can-use-dom' => [
        'version' => '0.1.0',
    ],
    'simplebar-core' => [
        'version' => '1.2.4',
    ],
    'simplebar/dist/simplebar.min.css' => [
        'version' => '6.2.5',
        'type' => 'css',
    ],
    'lodash-es' => [
        'version' => '4.17.21',
    ],
    'simplebar-core/dist/simplebar.min.css' => [
        'version' => '1.2.4',
        'type' => 'css',
    ],
    'jquery-sortablejs' => [
        'version' => '1.0.1',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'tinymce/models/dom/model' => [
        'version' => '6.8.3',
    ],
    'tinymce/themes/silver' => [
        'version' => '6.8.3',
    ],
    'tinymce/icons/default' => [
        'version' => '6.8.3',
    ],
    'tinymce/skins/ui/oxide/skin.min.css' => [
        'version' => '6.8.3',
        'type' => 'css',
    ],
    'tinymce/plugins/advlist' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/anchor' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/autolink' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/autoresize' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/autosave' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/charmap' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/code' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/codesample' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/directionality' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/emoticons' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/fullscreen' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/help' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/image' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/importcss' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/insertdatetime' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/link' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/lists' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/media' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/nonbreaking' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/pagebreak' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/preview' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/quickbars' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/save' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/searchreplace' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/table' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/template' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/visualblocks' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/visualchars' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/wordcount' => [
        'version' => '6.8.3',
    ],
    'tinymce/plugins/emoticons/js/emojis' => [
        'version' => '6.8.3',
    ],
    'tinymce/skins/ui/oxide/skin.css' => [
        'version' => '6.8.3',
        'type' => 'css',
    ],
    'tinymce/skins/ui/oxide/content.css' => [
        'version' => '6.8.3',
        'type' => 'css',
    ],
    'tinymce/skins/ui/oxide-dark/skin.css' => [
        'version' => '6.8.3',
        'type' => 'css',
    ],
    'tinymce/skins/ui/oxide-dark/content.css' => [
        'version' => '6.8.3',
        'type' => 'css',
    ],
    'tinymce/skins/content/dark/content.css' => [
        'version' => '6.8.3',
        'type' => 'css',
    ],
    'select2' => [
        'version' => '4.1.0-rc.0',
    ],
    'select2/dist/css/select2.min.css' => [
        'version' => '4.1.0-rc.0',
        'type' => 'css',
    ],
    'module-loader' => [
        'path' => './assets/js/module-loader.js',
    ],
    'module-user' => [
        'path' => './assets/module_scripts/user/module.js',
    ],
    'tom-select/dist/css/tom-select.bootstrap4.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
];
