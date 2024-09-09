<?php

	namespace Webprofy\Tools\Bitrix\Attribute\Action;
	
	use Webprofy\Tools\General\Container;
	use Webprofy\Tools\Bitrix\Attribute\Action\Actions;

	class UpdateActions extends Actions{
		protected $fillActions = array(
			'Webprofy\Tools\Bitrix\Attribute\Action\Update\SetAction',
			'Webprofy\Tools\Bitrix\Attribute\Action\Update\MathAction',
			'Webprofy\Tools\Bitrix\Attribute\Action\Update\StringAction',
			'Webprofy\Tools\Bitrix\Attribute\Action\Update\PhpAction',
		);
	}