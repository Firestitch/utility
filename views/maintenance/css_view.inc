<?
	class VIEW_MAINTENANCE_CSS extends VIEW {
	
		function __construct() {
			$this->set_route("maintenance","css");
			$this->set_form("/maintenance/docss/");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
			$this->set_title("");
		}
		
		function init() {
			$registry = REGISTRY::get_instance();
			
			$app_dir = APPLICATION::get_main_application_directory();
			
			
			
			
			
			/*
			
			require_once(MODEL_PATH::get_packages_directory()."sabberworm/CSSParser.php");
			
			$contents = "#mine {
				color: red;
				border-color: rgba(10, 100, 230, 0.3);
				outline-color: #222;
				background-color: #232323;
			}

			#yours {
				background-color: hsl(220, 10, 220);
			}";

			
			$oParser = new CSSParser($contents);
			$oDoc = $oParser->parse();			
			
			foreach($oDoc->getAllRuleSets() as $oRuleSet) {
				
				if(!$oRuleSet instanceof CSSSelector) {
					continue;
				}
				
				p($oRuleSet);
				
				die;
				
				$aSelector = $oRuleSet->getSelector();
				if($aSelector[0] === '#mine') {
					
					$aColorRule = $oRuleSet->getRules('color');
					$aValues = $aColorRule['color']->getValues();
					
					$this->assertSame('red', $aValues[0][0]);
					
					$aColorRule = $oRuleSet->getRules('background-');
					
					$aValues = $aColorRule['background-color']->getValues();
					
					$this->assertEquals(array('r' => new CSSSize(35.0), 'g' => new CSSSize(35.0), 'b' => new CSSSize(35.0)), $aValues[0][0]->getColor());
					
					$aColorRule = $oRuleSet->getRules('border-color');
					$aValues = $aColorRule['border-color']->getValues();
					
					$this->assertEquals(array('r' => new CSSSize(10.0), 'g' => new CSSSize(100.0), 'b' => new CSSSize(230.0), 'a' => new CSSSize(0.3)), $aValues[0][0]->getColor());
					
					$aColorRule = $oRuleSet->getRules('outline-color');
					
					$aValues = $aColorRule['outline-color']->getValues();
					
					$this->assertEquals(array('r' => new CSSSize(34.0), 'g' => new CSSSize(34.0), 'b' => new CSSSize(34.0)), $aValues[0][0]->getColor());
				}
			}
			foreach($oDoc->getAllValues('background-') as $oColor) {
				//if($oColor->getColorDescription() === 'hsl') {
				//	$this->assertEquals(array('h' => new CSSSize(220.0), 's' => new CSSSize(10.0), 'l' => new CSSSize(220.0)), $oColor->getColor());
				//}
			}
			foreach($oDoc->getAllValues('color') as $sColor) {
				//$this->assertSame('red', $sColor);
			}
			*/
		}		
	}