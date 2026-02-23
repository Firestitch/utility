<?php
namespace {$namespace}\Model;

{if !$primaryObjectId}
use Framework\Core\Model;
{/if}use {$namespace}\Dbo\{$pascalName}Dbo;
use \{$namespace}\Handler\{$pascalName}Handler;
{if $primaryObjectId}
use Framework\Db\Db;
{/if}
{if $primaryObjectId || $hasCreateDate || $hasModifyDate}
use Framework\Model\TimeModel;{/if}
{if $primaryObjectId && $hasModifyAccountId}

use Framework\Audit\Model\AuditorModel;{/if}
{if $hasGuid}
use Framework\Util\MiscUtil;

{/if}
use Framework\Model\Attribute\Describe;
use Framework\Model\Enum\TypeEnum;
use Framework\Model\Attribute\Property;
{if $hasState}
use {$namespace}\Enum\{$pascalName}StateEnum;
use Framework\Model\Attribute\Default\DefaultValue;
use Framework\Model\Attribute\Validation\ValidationRequired;
{/if}
{if count($primaryKeys) === 1 && $primaryObjectId}
use Framework\Model\Attribute\ToArray\ToArray;
{/if}


#[Describe(properties: [
])]
class {$pascalName}Model extends {if $primaryObjectId}\Framework\Object\Model\BaseObjectModel{else}Model{/if} {

  use Traits\{$pascalName}Trait{if $primaryObjectId}{/if};

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

  public function startAudit(?int $accountId = null, ?AuditorModel $auditorModel = null) {
    $this->setModifyAccountId($accountId);
    return parent::startAudit($accountId, $auditorModel);
  }
{/if}

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
	
		$this->getDbo({$pascalName}Dbo::class)->upsert();
{else}
{if $primaryObjectId}

		if($this->get{$primaryKey|pascalize}()) {
			$this->getDbo({$pascalName}Dbo::class)->update();
		} else {
			Db::transaction(function () {
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
				$this->getDbo({$pascalName}Dbo::class)->insert();
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
		$this->getDbo({$pascalName}Dbo::class)->update();
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
			$this->getDbo({$pascalName}Dbo::class)->insert();
		}
{/if}
{/if}
{/if}

		return $this;
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
