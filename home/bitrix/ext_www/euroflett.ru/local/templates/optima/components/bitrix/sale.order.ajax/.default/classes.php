<?
	namespace SimpleOrder;

	class SitePersonProperty extends PersonProperty{
		protected function showHtml(HTML $html){
			$property = $this->property;
			if($property['TYPE'] == 'LOCATION'){
				$html->show();
				return $this;
			}
			?>
				<div class="field">
					<div class="left"><?=$property['NAME']?> <?=$this->isRequired() ? '*' : ''?>:</div>
					<div class="right">
						<? $html->show() ?>
					</div>
					<div class="status">
					</div>
				</div>
			<?
			return $this;
		}
	}
