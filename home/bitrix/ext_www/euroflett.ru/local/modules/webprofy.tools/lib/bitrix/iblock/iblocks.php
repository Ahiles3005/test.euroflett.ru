<?
	namespace Webprofy\Tools\Bitrix\IBlock;

	use Webprofy\Tools\General\Container;

	class IBlocks extends Container{
		static function generate($info){
			$iblocks = new self();

			foreach($info as $info_){
				$iblocks->add(IBlock::generate($info_));
			}

			return $iblocks;
		}

		function mergeSelects(){
			$me = $this;
			$me->each(function($iblock1) use ($me){
				$me->each(function($iblock2) use ($me, $iblock1){
					$index1 = $iblock1->getIndex();
					$index2 = $iblock2->getIndex();

					if($index1 == $index2){
						return;
					}

					$select2 = $iblock2->getSelect();

					$relations1 = $iblock1->getRelations();
					$relations2 = $iblock2->getRelations();

					$select2more = $relations1->getOtherIBlockAttributes($iblock2);
					$select2->extend($select2more);
				});
			});

			return $this;
		}

		function getExampleTables(){
			$tables = array();
			$iblocks = new self();

			foreach($this as $iblock){
				$tables[] = $iblock->getExampleTable($iblocks);
				$iblocks->add($iblock);
			}

			return $tables;
		}

		function getValues($iblock, $attribute){
			$iblocks = new self();
			foreach($this as $iblock_){
				if($iblock_->getIndex() == $iblock->getIndex()){
					return $iblock_->getValues($iblocks, $attribute);
				}
				else{
					$iblock_->updateCurrentEntity($iblocks);
				}
				$iblocks->add($iblock_);
			}
			return null;
		}
	}