<?php

	namespace Webprofy\Tools\Bitrix\Attribute\Action;
	
	use Webprofy\Tools\General\Container;
	use Webprofy\Tools\Bitrix\Attribute\Action\Actions;

	use Webprofy\Tools\Bitrix\Attribute\Action\Compare\BetweenAction;
	use Webprofy\Tools\Bitrix\Attribute\Action\Compare\EqualAction;

	class CompareActions extends Actions{
		protected $fillActions = array(
			'Webprofy\Tools\Bitrix\Attribute\Action\Compare\EqualAction',
			'Webprofy\Tools\Bitrix\Attribute\Action\Compare\BetweenAction',
		);
	}