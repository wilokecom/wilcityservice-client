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
			'default' => ''
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