<?php
return array(
'document_root' => __DIR__,
'module_path' => array(),
'service_path' => array(
    __DIR__.'/../'
),
'library_path' => array(),
'modules' => array(),
'params' => array(),
'services' => array(
    'Youku' => array(
        'class' => \X\Service\Youku\Service::class,
        'enable' => true,
        'delay' => true,
        'params' => array(
            'apps' => array(
                'youkutestapp' => array(
                    'appid' => '********',
                ),
            )
        ),
    ),
),
);