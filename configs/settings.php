<?php
return array(
	'fields' => array(
		array(
			'type' => 'open_segment'
		),
		array(
			'type'    => 'textarea',
			'heading' => 'Secret Token',
			'name'    => 'wilcityservice_client[secret_token]',
			'id'      => 'secret_token',
			'default' => '',
			'desc'    => '<a target="_blank" href="https://documentation.wilcity.com/knowledgebase/how-to-auto-update-wilcity-wordpress-theme/">Where Is My Secret Token Code?</a>'
		),
		array(
			'type' => 'submit',
			'name' => 'Submit'
		),
		array(
			'type'    => 'close_segment'
		)
	)
);