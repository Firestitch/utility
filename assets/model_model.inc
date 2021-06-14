<?php
namespace Backend\Model;

{if !$primary_object_id}
use Framework\Core\Model;
{/if}use Backend\Dbo\{$pascal_model}Dbo;
{if $primary_object_id}
use Framework\Db\DB;
use Framework\Exception\SystemException;{/if}
{if $has_guid}
use Framework\Util\MiscUtil;

{/if}



class {$pascal_model}Model extends {if $primary_object_id}ObjectBaseModel{else}Model{/if} {

  use Traits\{$pascal_model}Trait;

{if $has_state}
	const STATE_ACTIVE	= "active";
	const STATE_DELETED	= "deleted";

	public static function getStates()	{ return [self::STATE_ACTIVE=>"Active",self::STATE_DELETED=>"Deleted"]; }
{/if}

	public function __construct() {
		parent::__construct({$pascal_model}Dbo::create());
  }
{foreach $consts as $const}{if $const!="state"}
  public function is{$const.const|pascalize}()	{ return $this->get{$const.field|pascalize}()==self::{$const.const}; }
{/if}
{/foreach}
{if $has_state}		public function getStateName()	{ return value(self::getStates(),$this->getState()); }
  public function delete()			{ return $this->setState(self::STATE_DELETED)->save(); }

  public function isStateDelete()	{ return $this->getState()==self::STATE_DELETED; }

	public function isStateActive()	{ return $this->getState()==self::STATE_ACTIVE; }
{/if}

	public function save() {
{if $has_multiple_keys}
		$this->set{$pascal_model}Id({$pascal_model}Dbq::create()
{foreach $keys as $key}
								->where("{$key}","=",$this->get_{$key}())
{/foreach}
								->one("{$lower_model}_id"));
{/if}
{if $primary_object_id}

			if($this->get{$primary_key|pascalize}()) {
				$this->dbo("{$lower_model}")->update();
			} else {
        DB::transaction(function () {
{if $has_state}
          if(!$this->getState())
            $this->setState(self::STATE_ACTIVE);

{/if}{if $has_guid}
          if(!$this->getGuid())
            $this->setGuid(MiscUtil::guid());

{/if}{if $has_create_date}
          if(!$this->getCreateDate())
            $this->setCreateDate(time());

{/if}
          $this->createObject();
          $this->dbo("{$lower_model}")->insert();
        });
			}

		$this->object(true)
			->setName($this->getName())
			->setPrimaryKeyword($this->getName())
			->setSecondaryKeyword($this->getName())
			->setModifyDate(DB::getDateTime()){if $has_state}

			->setActive(!$this->isStateDelete())
{/if}
			->save();

{else}

		if($this->get{$primary_key|pascalize}()) {
			$this->dbo("{$lower_model}")->update();
		} else {
{if $has_state}
      if(!$this->getState())
        $this->setState(self::STATE_ACTIVE);

{/if}{if $has_guid}
      if(!$this->getGuid())
        $this->setGuid(MiscUtil::guid());

{/if}{if $has_create_date}
      if(!$this->getCreateDate())
        $this->setCreateDate(time());

{/if}
			$this->dbo("{$lower_model}")->insert();
		}
{/if}
		return $this;
	}

	public function describe() {
		return [
{foreach from=$columns key=name item=column name=columns}
				"{$name}"=> [{if $column->isPrimary() || $column->isDataTypeDatetime() || $column->isDataTypeDate()}
{if $column->isDataTypeDatetime() || $column->isDataTypeDate()}

					"type"=>"{$column->getDataType()}",
{/if}{if $column->isPrimary()}

					"arry"=>[
						"name"=>"id"
					],
{/if}
{/if}{if $name=="state"}						"validations"=>[
						"key_exists"=>self::getStates()
					],
{/if}{if $column->isPrimary() || $name=="guid" || $name=="order" || $name=="modify_date" || $name=="create_date"}
					"diff"=>false
{/if}{if $column->isPrimary() || $name=="guid" || $name=="order" || $name=="state" || $name=="modify_date" || $name=="create_date" || $column->isDataTypeDatetime() || $column->isDataTypeDate()}					{/if}]{if !$smarty.foreach.columns.last},
{/if}{/foreach}
			];
	}
{if $primary_object_id}

	public function getObjectIdValue() {
		return $this->get_{$primary_key}();
	}

	public function setObjectIdValue($value) {
		$this->set_{$primary_key}($value);
	}

	public function getObjectClass() {
		return ObjectModel::CLASS_{$upper_model};
	}

  public function __toString() {
    return $this->getName();
  }
{/if}
}
