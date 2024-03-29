<?php
namespace {$namespace}\Model;

{if !$primaryObjectId}
use Framework\Core\Model;
{/if}use {$namespace}\Dbo\{$pascalName}Dbo;
use \{$namespace}\Handler\{$pascalName}Handler;
{if $primaryObjectId}
use Backend\Lib\Object\Traits\ObjectTrait;
use Framework\Db\Db;{/if}
{if $primaryObjectId || $hasCreateDate || $hasModifyDate}
use Framework\Model\TimeModel;{/if}
{if $primaryObjectId && $hasModifyAccountId}

use Framework\Audit\Model\AuditorModel;{/if}
{if $hasGuid}
use Framework\Util\MiscUtil;
{/if}


class {$pascalName}Model extends {if $primaryObjectId}\Framework\Object\Model\ObjectModel{else}Model{/if} {

  use Traits\{$pascalName}Trait{if $primaryObjectId}, ObjectTrait{/if};

{if $hasState}
	const STATE_ACTIVE	= "active";
	const STATE_DELETED	= "deleted";

	public static function getStates()	{ 
		return [self::STATE_ACTIVE=>"Active",self::STATE_DELETED=>"Deleted"]; 
	}

{/if}
	public function __construct() {
		parent::__construct({$pascalName}Dbo::create());
  }	
{foreach $consts as $const}{if $const!="state"}
  public function is{$const.const|pascalize}()	{ 
		return $this->get{$const.field|pascalize}() === self::{$const.const}; 
	}

{/if}
{/foreach}
{if $hasState}
	public function getStateName()	{ 
		return value(self::getStates(),$this->getState()); 
	}

  public function delete() { 
		return $this
			->setState(self::STATE_DELETED)
			->save(); 
	}

  public function isStateDeleted()	{ 
		return $this->getState() === self::STATE_DELETED; 
	}

	public function isStateActive()	{ 
		return $this->getState() === self::STATE_ACTIVE; 
	}
{/if}
{if $primaryObjectId && $hasModifyAccountId}

  /**
   * @return static
   */
  public function startAudit(?int $accountId = null, ?AuditorModel $auditorModel = null) {
    $this->setModifyAccountId($accountId);
    return parent::startAudit($accountId, $auditorModel);
  }
{/if}

	/**
	 * @return static
	 */
	public function save() { {if $uniqueIndex}

		$this->set{$pascalName}Id(
			{$pascalName}Dbq::create()
{foreach $uniqueIndex["columns"] as $key}
				->where("{$key}","=",$this->get{$key|pascalize}())
{/foreach}
				->one("{$lowerModel}_id")
		);
{/if}
{if !$uniqueIndex}
{if $hasModifyDate}
    
		$this->setModifyDate(TimeModel::now());
{/if}
{if count($primaryKeys)>1}
	
		$this->getDbo("{$dboName}")->upsert();
{else}
{if $primaryObjectId}

		if($this->get{$primaryKey|pascalize}()) {
			$this->getDbo("{$dboName}")->update();
		} else {
			DB::transaction(function () {
{if $hasState}
				if(!$this->getState()) {
					$this->setState(self::STATE_ACTIVE);
				}

{/if}{if $hasGuid}
				if(!$this->getGuid()) {
					$this->setGuid(MiscUtil::guid());
				}

{/if}{if $hasCreateDate}
				if(!$this->getCreateDate()) {
					$this->setCreateDate(TimeModel::now());
				}

{/if}
				$this->createObject();
				$this->getDbo("{$dboName}")->insert();
			});
		}

	$this
		->object(true)
		->setName((string)$this)
		->setModifyDate(TimeModel::now()){if $hasState}

		->setActive(!$this->isStateDeleted())
{/if}
		->save();

{else}

	if($this->get{$primaryKey|pascalize}()) {
		$this->getDbo("{$dboName}")->update();
	} else {
{if $hasState}
		if(!$this->getState()) {
			$this->setState(self::STATE_ACTIVE);
		}

{/if}{if $hasGuid}
		if(!$this->getGuid()) {
			$this->setGuid(MiscUtil::guid());
		}

{/if}{if $hasCreateDate}
		if(!$this->getCreateDate()) {
			$this->setCreateDate(TimeModel::now());
		}

{/if}
			$this->getDbo("{$dboName}")->insert();
		}
{/if}
{/if}
{/if}

		return $this;
	}

	public function describe() {
		return [
{foreach from=$columns key=name item=column name=columns}
			"{$name|camelize}" => [{if $column->isPrimary() || $column->isDataTypeDatetime() || $column->isDataTypeDate()}
{if $column->isDataTypeDatetime() || $column->isDataTypeDate()}

				"type" => "{$column->getDataType()}",
{/if}{if $column->isPrimary() && count($primaryKeys) === 1}

				"arry" => [
					"name"=>"id"
				],
{/if}
{/if}{if $name=="state"}						
				"validations" => [
					"keyExists" => self::getStates()
				],
{/if}{if (preg_match("/_id$/",$name) && !$column->isPrimary() && !$column->isNotNull()) || $name === "name"}						
				"validations" => [
					"required" => true
				],
{/if}{if $column->isPrimary() || $name=="guid" || $name=="order" || $name=="modify_date" || $name=="create_date"}
				"diff" => false
{/if}{if $column->isPrimary() || $name=="guid" || $name=="order" || $name=="state" || $name=="modify_date" || $name=="create_date" || $column->isDataTypeDatetime() || $column->isDataTypeDate()}{/if}    ]{if !$smarty.foreach.columns.last},
{/if}{/foreach}
		
		];
	}
{if $primaryObjectId}

	public function getObjectIdValue() {
		return $this->get{$primaryKey|pascalize}();
	}

	public function setObjectIdValue($value) {
		return $this->set{$primaryKey|pascalize}($value);
	}

	public function getObjectClass() {
		return ObjectModel::CLASS_{$upperModel};
	}

  public function __toString() {
    return (string){if $hasName}$this->getName(){else}$this->get{$primaryKey|pascalize}(){/if};
  }

	public function getAuditRelatedObjects() {
		return [];
	}
{/if}

  public static function getModelHandler() {
    return {$pascalName}Handler::class;
  }
}
