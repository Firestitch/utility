<?
	class {if $framework}B{/if}CMODEL_{$upper_model} extends {$extends} {
{if $has_state}

		const STATE_ACTIVE	= "active";
		const STATE_DELETED	= "deleted";

		public static function get_states()	{ return [self::STATE_ACTIVE=>"Active",self::STATE_DELETED=>"Deleted"]; }
{/if}

		public function __construct() {
			parent::__construct(DBO_{$upper_model}::create());
		}
{if $primary_object_id}
		public function set_object($value) { return $this->data("object",$value); }

		public function get_object($hmodel=false) {
			if($hmodel && !$this->has_data("object") && $this->get_{$primary_key}()) {
				$hmodel = is_a($hmodel,"HMODEL") ? $hmodel : HMODEL_OBJECT::create(false);
				$this->data("object",$hmodel->where("objects.object_id","=",$this->get_{$primary_key}())
												->get());
			}
			return $this->data("object");
		}
{/if}
{foreach $consts as $const}{if $const!="state"}		public function is_{$const.const|lower}()	{ return $this->get_{$const.field}()==self::{$const.const}; }{/if}
{/foreach}
{foreach $columns as $name=>$column}{if $column->is_data_type("CHAR") || $name=="state"}		public function get_{$name}_name()	{ return value(self::get_{$name}s(),$this->get_{$name}()); }
{/if}{/foreach}
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
{if $primary_object_id}

			try {

				DB::start_transaction();

				if(self::get_{$primary_key}()) {
					$this->dbo("{$lower_model}")->update();
				} else {
					$this->create_object();
					$this->dbo("{$lower_model}")->insert();
				}

				if(!DB::complete_transaction())
					throw new Exception("Error saving ".get_class());

			} catch(CMODEL_SYSTEM_EXCEPTION $e) {
				DB::complete_transaction();
				throw $e;
			}

			$this->get_object(true)
				->set_name($this->get_name())
				->set_primary_keyword($this->get_name())
				->set_secondary_keyword($this->get_name())
				->set_modify_date(DB::get_date_time())
				->set_active($this->is_state_active())
				->save();

{else}

			if(self::get_{$primary_key}()) {
				$this->dbo("{$lower_model}")->update();
			} else {
				$this->dbo("{$lower_model}")->insert();
			}
{/if}
			return $this;
		}

		public function describe() {
			return [{foreach from=$columns key=name item=column name=columns}

					"{$name}"=> [
{if $column->is_primary() || $column->is_data_type_datetime() || $column->is_data_type_date()}						"arry"=>[{if $column->is_primary()}"name"=>"id"{/if}{if $column->is_data_type_datetime() || $column->is_data_type_date()}"format"=>"iso8601"{/if}],
{/if}
{if $name=="state"}						"default"=>self::STATE_ACTIVE,
{/if}
{if $name=="guid"}						"default"=>"guid",
{/if}
{if $name=="create_date"}						"default"=>"datetime",
{/if}
{if $column->is_data_type_datetime() || $column->is_data_type_date()}						"sanitize"=>"time",
{/if}						"validations"=>[{if $name=="state"}"required"=>true,
										"key_exists"=>self::get_states(){/if}]{if $column->is_primary() || $name=="guid" || $name=="order" || $name=="modify_date" || $name=="create_date"},
						"diff"=>false{/if}

					]{if !$smarty.foreach.columns.last},{/if}
{/foreach}

			];
		}
	}