<?
	class {if $framework}B{/if}CMODEL_{$upper_model} extends {$extends} {
{if $has_state}
		
		const STATE_ACTIVE	= "active";
		const STATE_DELETED	= "deleted";
				
		public static function get_states()	{ return array(self::STATE_ACTIVE=>"Active",self::STATE_DELETED=>"Deleted"); }
{/if}

		public function __construct() {
			parent::__construct();
			return $this->register_dbo(DBO_{$upper_model}::create());
		}
		
{foreach $consts as $const}{if $const!="state"}		public function is_{$const.const|lower}()	{ return $this->get_{$const.field}()==self::{$const.const}; }{/if}
{/foreach}
{foreach $columns as $name=>$column}{if $column->is_data_type("CHAR") || $name=="state"}		public function get_{$name}_name()	{ return value(self::get_{$name}s(),$this->get_{$name}()); }
{/if}{/foreach}
		public function get_manage_url()	{ return "/manage/{$lower_model|replace:'_':''}/{$id}:".$this->get_{$primary_key}()."/"; }
		public function get_url()			{ return "/{$lower_model|replace:'_':''}s/{$lower_model|replace:'_':''}/{$id}:".$this->get_{$primary_key}()."/"; }
{if $has_state}		public function delete()			{ return $this->set_state(self::STATE_DELETED)->save(); }	
		public function is_state_delete()	{ return $this->get_state()==self::STATE_DELETED; }		
		public function is_state_active()	{ return $this->get_state()==self::STATE_ACTIVE; }		
{/if}

		public function save() {
{if $has_multiple_keys}
			$this->set_{$lower_model}_id(DBQ_{$upper_model}::create()
{foreach $keys as $key}
									->where("{$key}","=",$this->get_{$key}())
{/foreach}
									->one("{$lower_model}_id"));
{/if}

{if $extended}
			try {
				
				DB::start_transaction();
				parent::save();

{/if}
			if(is_numeric(self::get_{$primary_key}())) {
				$this->dbo("{$lower_model}")->update();
			} else {
{if $has_guid}
				if(!$this->get_guid())
					$this->set_guid(MISC_UTIL::get_guid());
					
{/if}
{if $has_state}
				if(!$this->get_state())
					$this->set_state(self::STATE_ACTIVE);
					
{/if}
{if $has_create_date}
				if(!$this->get_create_date())
					$this->set_create_date(DB::get_date_time());
					
{/if}
{if $extended}
				$this->dbo("{$lower_model}")
					->set_{$extend_id}(parent::get_{$extend_id}())
					->insert();
						
{else}				$this->dbo("{$lower_model}")->insert();
{/if}
			}
{if $extended}

				if(!DB::complete_transaction())
					throw new Exception("Error saving ".get_class());
			
			} catch(Exception $e) {
				DB::complete_transaction();
				throw $e;
			}
{/if}		
			return $this;
		}

		public function arry($arry=array()) {

			$arry = CMODEL_OBJECTER::create($this,
											array(	{foreach $columns as $name=>$column}{if $column->is_primary()}"id"=>"get_{$name}",
{else}"get_{$name}{if $column->is_data_type_datetime()}|iso8601{/if}",
{/if}													{/foreach}))->arry();
{if $extended}			$arry += parent::arry();
			{/if}

			return $arry;
		}		
	}