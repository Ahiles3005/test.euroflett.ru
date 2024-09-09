<?php

	namespace Webprofy\Bitrix\Attribute;

	use Webprofy\General\Container;
	use Webprofy\Bitrix\Attribute\Attribute;
	use Webprofy\Bitrix\Attribute\AttributesTree;
	use Webprofy\Bitrix\IBlock\IBlocks;
	
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