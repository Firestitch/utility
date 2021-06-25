<?php
namespace Backend\Model;

{if !$primaryObjectId}
use Framework\Core\Model;
{/if}use Backend\Dbo\{$pascalModel}Dbo;
{if $primaryObjectId}
use Framework\Db\DB;
use Framework\Exception\SystemException;{/if}
{if $hasGuid}
use Framework\Util\MiscUtil;

{/if}



class {$pascalModel}Model extends {if $primaryObjectId}ObjectBaseModel{else}Model{/if} {

  use Traits\{$pascalModel}Trait;

{if $hasState}
	const STATE_ACTIVE	= "active";
	const STATE_DELETED	= "deleted";

	public static function getStates()	{ return [self::STATE_ACTIVE=>"Active",self::STATE_DELETED=>"Deleted"]; }
{/if}

	public function __construct() {
		parent::__construct({$pascalModel}Dbo::create());
  }
{foreach $consts as $const}{if $const!="state"}
  public function is{$const.const|pascalize}()	{ return $this->get{$const.field|pascalize}()==self::{$const.const}; }
{/if}
{/foreach}
{if $hasState}		public function getStateName()	{ return value(self::getStates(),$this->getState()); }
  public function delete()			{ return $this->setState(self::STATE_DELETED)->save(); }

  public function isStateDelete()	{ return $this->getState()==self::STATE_DELETED; }

	public function isStateActive()	{ return $this->getState()==self::STATE_ACTIVE; }
{/if}

	public function save() {
{if $hasMultipleKeys}
		$this->set{$pascalModel}Id({$pascalModel}Dbq::create()
{foreach $keys as $key}
								->where("{$key}","=",$this->get_{$key}())
{/foreach}
								->one("{$lowerModel}_id"));
{/if}
{if $primaryObjectId}

			if($this->get{$primaryKey|pascalize}()) {
				$this->dbo("{$lowerModel}")->update();
			} else {
        DB::transaction(function () {
{if $hasState}
          if(!$this->getState())
            $this->setState(self::STATE_ACTIVE);

{/if}{if $hasGuid}
          if(!$this->getGuid())
            $this->setGuid(MiscUtil::guid());

{/if}{if $hasCreateDate}
          if(!$this->getCreateDate())
            $this->setCreateDate(time());

{/if}
          $this->createObject();
          $this->dbo("{$lowerModel}")->insert();
        });
			}

		$this->object(true)
			->setName($this->getName())
			->setPrimaryKeyword($this->getName())
			->setSecondaryKeyword($this->getName())
			->setModifyDate(DB::getDateTime()){if $hasState}

			->setActive(!$this->isStateDelete())
{/if}
			->save();

{else}

		if($this->get{$primaryKey|pascalize}()) {
			$this->dbo("{$lowerModel}")->update();
		} else {
{if $hasState}
      if(!$this->getState())
        $this->setState(self::STATE_ACTIVE);

{/if}{if $hasGuid}
      if(!$this->getGuid())
        $this->setGuid(MiscUtil::guid());

{/if}{if $hasCreateDate}
      if(!$this->getCreateDate())
        $this->setCreateDate(time());

{/if}
			$this->dbo("{$lowerModel}")->insert();
		}
{/if}
		return $this;
	}

	public function describe() {
		return [
{foreach from=$columns key=name item=column name=columns}
				"{$name|camelize}"=> [{if $column->isPrimary() || $column->isDataTypeDatetime() || $column->isDataTypeDate()}
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
{if $primaryObjectId}

	public function getObjectIdValue() {
		return $this->get{$primaryKey|pascalize}();
	}

	public function setObjectIdValue($value) {
		$this->set{$primaryKey|pascalize}($value);
	}

	public function getObjectClass() {
		return ObjectModel::CLASS_{$upperModel};
	}

  public function __toString() {
    return (string)$this->getName();
  }
{/if}
}
