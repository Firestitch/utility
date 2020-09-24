<?
namespace Backend\Model;

{if !$primary_object_id}
use Framework\Core\Model;
{/if}use Backend\Dbo\{$pascal_model}Dbo;
{if $primary_object_id}
use Framework\Db\DB;
use Framework\Exception\SystemException;{/if}

class {$pascal_model}Model extends {if $primary_object_id}BaseObjectModel{else}Model{/if} {

    use Traits\{$pascal_model}Trait;

{if $has_state}
		const STATE_ACTIVE	= "active";
		const STATE_DELETED	= "deleted";

		public static function get_states()	{ return [self::STATE_ACTIVE=>"Active",self::STATE_DELETED=>"Deleted"]; }
{/if}

		public function __construct() {
			parent::__construct({$pascal_model}Dbo::create());
    }
{foreach $consts as $const}{if $const!="state"}
		public function is_{$const.const|lower}()	{ return $this->get_{$const.field}()==self::{$const.const}; }{/if}
{/foreach}
{if $has_state}		public function get_state_name()	{ return value(self::get_states(),$this->get_state()); }
		public function delete()			{ return $this->set_state(self::STATE_DELETED)->save(); }
		public function is_state_delete()	{ return $this->get_state()==self::STATE_DELETED; }
		public function is_state_active()	{ return $this->get_state()==self::STATE_ACTIVE; }
{/if}

		public function save() {
{if $has_multiple_keys}
			$this->set_{$lower_model}_id({$pascal_model}Dbq::create()
{foreach $keys as $key}
									->where("{$key}","=",$this->get_{$key}())
{/foreach}
									->one("{$lower_model}_id"));
{/if}
{if $primary_object_id}

			try {

				DB::start_transaction();

				if($this->get_{$primary_key}()) {
					$this->dbo("{$lower_model}")->update();
				} else {
					$this->create_object();
					$this->dbo("{$lower_model}")->insert();
				}

				if(!DB::complete_transaction())
          throw new \Exception("Error saving ".get_class());

			} catch(SystemException $e) {
				DB::complete_transaction();
				throw $e;
			}

			$this->object(true)
				->set_name($this->get_name())
				->set_primary_keyword($this->get_name())
				->set_secondary_keyword($this->get_name())
				->set_modify_date(DB::get_date_time())
				->set_active(!$this->is_state_delete())
				->save();

{else}

			if($this->get_{$primary_key}()) {
				$this->dbo("{$lower_model}")->update();
			} else {
				$this->dbo("{$lower_model}")->insert();
			}
{/if}
			return $this;
		}

		public function describe() {
			return [
{foreach from=$columns key=name item=column name=columns}
					"{$name}"=> [{if $column->is_primary() || $column->is_data_type_datetime() || $column->is_data_type_date()}
{if $column->is_data_type_datetime() || $column->is_data_type_date()}

						"type"=>"{$column->get_data_type()}",
{/if}{if $column->is_primary()}

						"arry"=>[
							"name"=>"id"
						],
{/if}
{/if}{if $name=="state"}

						"default"=>self::STATE_ACTIVE,
{/if}{if $name=="guid"}
						"default"=>"guid",
{/if}{if $name=="create_date"}
						"default"=>"datetime",
{/if}{if $name=="state"}						"validations"=>[
							"required"=>true,
							"key_exists"=>self::get_states()
						],
{/if}{if $column->is_primary() || $name=="guid" || $name=="order" || $name=="modify_date" || $name=="create_date"}
						"diff"=>false
{/if}{if $column->is_primary() || $name=="guid" || $name=="order" || $name=="state" || $name=="modify_date" || $name=="create_date" || $column->is_data_type_datetime() || $column->is_data_type_date()}					{/if}]{if !$smarty.foreach.columns.last},
{/if}{/foreach}
				];
		}
{if $primary_object_id}

		public function get_object_id_value() {
			return $this->get_{$primary_key}();
		}

		public function set_object_id_value($value) {
			$this->set_{$primary_key}($value);
		}

		public function get_object_class() {
			return CMODEL_OBJECT::CLASS_{$upper_model};
		}

	    public function __toString() {
	        return $this->get_name();
	    }
{/if}
	}
