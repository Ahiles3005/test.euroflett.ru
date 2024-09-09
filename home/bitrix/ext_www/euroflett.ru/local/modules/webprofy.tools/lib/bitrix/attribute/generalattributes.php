<?php

	namespace Webprofy\Tools\Bitrix\Attribute;

	use Webprofy\Tools\General\Container;
	use Webprofy\Tools\Bitrix\Attribute\Attribute;
	use Webprofy\Tools\Bitrix\Attribute\AttributesTree;
	use Webprofy\Tools\Bitrix\IBlock\IBlocks;
	
	class GeneralAttributes extends Container{

		protected $iblock;

		function __construct($iblock = null){
			parent::__construct();
			$this->iblock = $iblock;
		}
		
		function setIBlock($iblock){
			$this->iblock = $iblock;
			return $this;
		}	

		function getSelectFields($clearLogic = false){}

		function setOtherIBlocksValues(IBlocks $otherIBlocks = null){
			$this->each(function($one) use ($otherIBlocks){
				if($one instanceof AttributesTree){
					$one->setOtherIBlocksValues($otherIBlocks);
				}

				if($one instanceof Attribute){
					$one->getAction()->getValue()->setFromOtherIBlocks($otherIBlocks);
				}
			});
		}
	}