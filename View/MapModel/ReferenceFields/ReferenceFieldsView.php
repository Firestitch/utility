<?php

namespace Utility\View\MapModel\ReferenceFields;

use Framework\Core\View;
use Framework\Util\DebugUtil;
use Utility\Model\DbGeneratorModel;
class ReferenceFieldsView extends View
{
    protected $_referenceModel = "";
    protected $_referenceModelColumn = "";
    protected $_referenceColumns = array();
    function __construct()
    {
        $this->setTemplate("./ReferenceFieldsTemplate.php");
        $this->disableAuthorization();
    }
    function init()
    {
        DebugUtil::enableFormatHtml();
        $referenceModel = $this->post("reference_model");
        if ($referenceModel) {
            $this->_referenceModel = $referenceModel;
        }
        if ($this->_referenceModel) {
            $this->_referenceColumns = DbGeneratorModel::getDbo($this->_referenceModel)->getColumns();
        }
        $referenceModelColumnList = array();
        foreach ($this->_referenceColumns as $name => $column) {
            $referenceModelColumnList[$name] = $name;
        }
        $this->setVar("referenceModel", $this->_referenceModel);
        $this->setVar("referenceColumns", $this->_referenceColumns);
        $this->setVar("referenceModelColumnList", $referenceModelColumnList);
        $this->setVar("referenceModelColumn", $this->_referenceModelColumn);
    }
}