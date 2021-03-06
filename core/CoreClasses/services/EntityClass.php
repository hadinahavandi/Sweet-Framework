<?php
/*
 *@Author:Hadi AmirNahavandi
*@Last Update:2014/5/07
*/
namespace core\CoreClasses\services;

use core\CoreClasses\db\DBField;
use core\CoreClasses\db\dbquery;
use core\CoreClasses\db\QueryLogic;
use core\CoreClasses\db\selectQuery;
use core\CoreClasses\db\updateQuery;

class EntityClass  extends ModuleClass{
    public static $ID='id';
    private $id;
    private $Fields;
    private $TableFields;
    private $FieldInfos;
    private $TableTitle;
    private $TitleFieldName="id";


    protected function addTableField($ID,$Name)
    {
        if($this->TableFields==null)
            $this->TableFields=array();
        $this->TableFields[$ID]=$Name;
    }
    public function getTableField($ID)
    {
        if($this->TableFields==null && key_exists($ID,$this->TableFields))
            return $this->TableFields[$ID];
        return null;
    }
    public function getTableFieldID($Name)
    {
//        print_r($this->TableFields);
        if($this->TableFields==null)
            return null;
        $ID=array_search($Name,$this->TableFields);
        if($ID===false)
            return -1;
        return $ID;
    }
    /**
     * @return string
     */
    public function getTitleFieldName()
    {
        return $this->TitleFieldName;
    }
    /**
     * @return string
     */
    public function getTitleField()
    {
        return $this->getField($this->getTitleFieldName());
    }
    /**
     * @param string $TitleFieldName
     */
    public function setTitleFieldName($TitleFieldName)
    {
        $this->TitleFieldName = $TitleFieldName;
    }

    /**
     * @return mixed
     */
    public function getTableTitle()
    {
        return $this->TableTitle;
    }

    /**
     * @param mixed $TableTitle
     */
    protected function setTableTitle($TableTitle)
    {
        $this->TableTitle = $TableTitle;
    }


    /**
     * @return FieldInfo
     */
    public function getFieldInfo($FieldName)
    {

        if(key_exists($FieldName,$this->FieldInfos))
            return $this->FieldInfos[$FieldName];
        else
        {
            if((substr($FieldName,strlen($FieldName)-3,3)=="_to"))
            {
                $FieldName2=substr($FieldName,0,strlen($FieldName)-3);
                $fInf=$this->getFieldInfo($FieldName2);
                $fInf2=$fInf->getCopy();
                $fInf2->setTitle($fInf2->getTitle() . " تا");
                return $fInf2;
            }
            elseif((substr($FieldName,strlen($FieldName)-5,5)=="_from"))
            {
                $FieldName2=substr($FieldName,0,strlen($FieldName)-5);
                $fInf=$this->getFieldInfo($FieldName2);
                $fInf2=$fInf->getCopy();
                $fInf2->setTitle($fInf2->getTitle() . " از");
                return $fInf2;
            }
            else
            {
                $fInf=new FieldInfo();
                $fInf->setTitle($FieldName);
                return $fInf;
            }
        }
    }

    /**
     * @param FieldInfo $FieldInfo
     * @param string $FieldName
     */
    protected function setFieldInfo($FieldName,FieldInfo $FieldInfo)
    {
        $this->FieldInfos[$FieldName] = $FieldInfo;
    }
    /**
     * @var dbquery
     */
    private $Database;
	private $TableName;
    /**
     * @var updateQuery
     */
    private $UpdateQuery;
    /**
     * @var selectQuery
     */
    private $SelectQuery;

    /**
     * @param updateQuery $UpdateQuery
     */
    protected function setUpdateQuery($UpdateQuery)
    {
        $this->UpdateQuery = $UpdateQuery;
    }

    /**
     * @param selectQuery $SelectQuery
     */
    protected function setSelectQuery($SelectQuery)
    {
        $this->SelectQuery = $SelectQuery;
    }

    /**
     * @param insertQuery $InsertQuery
     */
    protected function setInsertQuery($InsertQuery)
    {
        $this->InsertQuery = $InsertQuery;
    }

    /**
     * @return updateQuery
     */
    protected function getUpdateQuery()
    {
        return $this->UpdateQuery;
    }

    /**
     * @return selectQuery
     */
    protected function getSelectQuery()
    {
        return $this->SelectQuery;
    }

    /**
     * @return insertQuery
     */
    protected function getInsertQuery()
    {
        return $this->InsertQuery;
    }
    /**
     * @var insertQuery
     */
    private $InsertQuery;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->SelectQuery=$this->getDatabase()->Select(array("*"))->From($this->getTableName())->Where()->Equal("id",$id)->AndLogic()->Smaller("deletetime", "1");
        $this->SelectQuery=$this->SelectQuery->setLimit("0,1");
//        echo $this->SelectQuery->getQueryString();
        $result=$this->SelectQuery->ExecuteAssociated();

        if($result!=null)
            $this->loadFromArray($result[0]);
        else
            $this->loadFromArray(array());

    }
    public function loadFromArray(array $result)
    {
        if($result!=null && count($result)>0)
        {
            $this->Fields=$result;
            $this->id=$result['id'];
        }
        else
        {
            $this->Fields="";
            $this->id=-1;
        }
    }
	protected function getSelect(array $FieldstoSelect,array $FieldValues,array $Logics=null)
	{
		$Database=$this->Database;
		$Query=$Database->Select($FieldstoSelect)->From($this->TableName);
		$NotNullIndex=0;
        $Query=$Query->Where()->Equal("1", "1");
		if(!is_null($FieldValues) && count($FieldValues)>0)
		{
			for($i=0;$i<count($FieldValues);$i++)
			{
				if(!is_null($FieldValues[$i]['value']))
				{
					$Query=$Query->AndLogic();
					if($Logics===null || count($Logics)==0)
						$Query=$Query->Equal($FieldValues[$i]['name'], $FieldValues[$i]['value']);
					else 
						if($Logics[$i]==1)
							$Query=$Query->Equal($FieldValues[$i]['name'], $FieldValues[$i]['value']);
						elseif($Logics[$i]==2)
							$Query=$Query->Like($FieldValues[$i]['name'], $FieldValues[$i]['value']);
					$NotNullIndex++;
				}
			}

		}
//  		echo $Query->getQueryString();
		return $Query->ExecuteAssociated();
		
	}
    protected function getField($FieldName)
    {
        if($this->Fields==null || !is_array($this->Fields))
            return null;
        if(key_exists($FieldName,$this->Fields))
            return $this->Fields[$FieldName];
        else
            return null;
    }
    protected function setField($FieldName,$Value)
    {
        if(!is_array($this->Fields))
            $this->Fields=array();
       $this->Fields[$FieldName]=$Value;
    }
    public function Remove()
    {
        $this->UpdateQuery=$this->getDatabase()->Update($this->getTableName())
            ->Set("deletetime", time())
            ->Where()->Smaller("deletetime", "1")->AndLogic()->Equal("id",$this->id);
        //echo $this->UpdateQuery->getQueryString();
        //die();
        $this->UpdateQuery->Execute();
    }

    public function Find(QueryLogic $QueryObject)
    {
        $resFields="*";
        if($QueryObject->getResultFields()!=null)
        {
            $fields=$QueryObject->getResultFields();
            $resFields=$fields[0];
            for($i=1;$i<count($fields);$i++)
                $resFields.="," . $fields[$i];
        }
        $this->SelectQuery=$this->getDatabase()->Select(array($resFields))->From($this->getTableName())->Where()->Equal("1","1")->AndLogic();
        $conds=$QueryObject->getConditions();
        if($conds!=null)
            for($i=0;$i<count($conds);$i++)
                $this->SelectQuery=$this->SelectQuery->AddFieldCondition($conds[$i]);
        $OrderByFields=$QueryObject->getOrderByFields();
        $IsDescendings=$QueryObject->getIsDescendings();
        for($i=0;$OrderByFields!==null && $i<count($OrderByFields);$i++)
            $this->SelectQuery=$this->SelectQuery->AddOrderBy($OrderByFields[$i], $IsDescendings[$i]);
            $this->SelectQuery=$this->SelectQuery->setLimit("0,1");
        $this->SelectQuery=$this->SelectQuery->AndLogic()->Smaller("deletetime", "1");
        //echo $this->SelectQuery->getQueryString();
        //die();
        $result=$this->SelectQuery->ExecuteAssociated();
        if($result!=null)
         $this->loadFromArray($result[0]);
        else
            $this->loadFromArray(null);
    }

    function __call($func, $params){
        if(substr($func,0,3)=="get" && $func!="getID" && $func!="getDatabase" && $func!="getTableName" && $func!="getField" && $func!="getSelect" && $func!="getJsFilesDirectory" && $func!="getModuleDirectory" && $func!="getPHPFilesDirectory" && $func!="getTextsDirectory")
            return $this->getField(strtolower(substr($func,3)));
//        if(substr($func,0,3)=="set" && $func!="setID" && $func!="setDatabase" && $func!="setTableName" && $func!="setField" && $func!="setSelect" && $func!="setJsFilesDirectory" && $func!="setModuleDirectory" && $func!="setPHPFilesDirectory" && $func!="setTextsDirectory")
//            return $this->setField(strtolower(substr($func,3)),$params[0]);
    }
    public function GetArray()
    {
        $Array=array();
        $FieldIDs=array_keys($this->TableFields);
        $AllCount1 = count($FieldIDs);
        $Array['id']=$this->getId();
        for ($i = 0; $i < $AllCount1; $i++) {
            $field=$this->TableFields[$FieldIDs[$i]];
            $Array[$field]=$this->getField($field);
        }
        return $Array;
    }
    /**
     * @param QueryLogic $QueryObject
     * @return EntityClass[]
     */
    public function FindAll(QueryLogic $QueryObject)
    {
        $resFields="*";
        if($QueryObject->getResultFields()!=null)
        {
            $fields=$QueryObject->getResultFields();
            $resFields=$fields[0];
            for($i=1;$i<count($fields);$i++)
                $resFields.="," . $fields[$i];
        }
        $this->SelectQuery=$this->getDatabase()->Select(array($resFields))->From($this->getTableName())->Where()->Smaller("deletetime", "1");
        $this->fillSelectParams($QueryObject);
//        echo $this->SelectQuery->getQueryString() . "\n<br>";
//        die();
        $results= $this->SelectQuery->ExecuteAssociated();
        $Objects=array();
        for($i=0;$i<count($results);$i++)
        {
            $class=get_class($this);
            $Objects[$i]=new $class($this->Database->getDBAccessor(),$this->TableName);
            $Objects[$i]->loadFromArray($results[$i]);

        }
        return $Objects;
    }

    /**
     * @param QueryLogic $QueryObject
     * @return EntityClass
     */
    public function FindOne(QueryLogic $QueryObject)
    {
        $QueryObject->setLimit("0,1");
        $Objects=$this->FindAll($QueryObject);
        if($Objects!=null && is_array($Objects) && count($Objects)==1)
            return $Objects[0];
        else
            return null;
    }
    /**
     * @param QueryLogic $QueryObject
     * @return int
     */
    public function FindAllCount(QueryLogic $QueryObject)
    {
        $resFields="count(*) c";
        $this->SelectQuery=$this->getDatabase()->Select(array($resFields))->From($this->getTableName())->Where()->Smaller("deletetime", "1");
        $this->fillSelectParams($QueryObject);
//        echo $this->SelectQuery->getQueryString();
        $results= $this->SelectQuery->ExecuteAssociated();
        if($results==null || !is_array($results) || count($results)<=0)
            return 0;
        else
            return $results[0]['c'];
    }
    protected function fillSelectParams(QueryLogic $QueryObject)
    {
        $this->SelectQuery=$this->AddSelectParamsToQuery($this->SelectQuery,$QueryObject);

        $this->SelectQuery=$this->SelectQuery->AndLogic()->Smaller(new DBField($this->getTableName() . ".deletetime",true), "1");
    }

    protected function AddSelectParamsToQuery(selectQuery $Query,QueryLogic $QueryObject)
    {
        $conds=$QueryObject->getConditions();
        if($conds!=null)
        {
            $Query->AndLogic()->OpenParenthesis();
            for($i=0;$i<count($conds);$i++)
            {
                if($i>0)
                    $Query=$Query->AndLogic();
                $Query->AddFieldCondition($conds[$i]);
            }

            $Query->CloseParenthesis();
        }

        $OrderByFields=$QueryObject->getOrderByFields();
        $IsDescendings=$QueryObject->getIsDescendings();
        for($i=0;$OrderByFields!==null && $i<count($OrderByFields);$i++)
            $Query=$Query->AddOrderBy($OrderByFields[$i], $IsDescendings[$i]);
        $Limit=$QueryObject->getLimit();
        if($Limit!==null)
            $Query=$Query->setLimit($Limit);
        return $Query;
    }
    private function InsertSave()
    {
        $this->InsertQuery=$this->getDatabase()->InsertInto($this->getTableName());
        $FieldNames=array_keys($this->Fields);
        for($i=0;$i<count($FieldNames);$i++)
            $this->InsertQuery->Set($FieldNames[$i],$this->Fields[$FieldNames[$i]]);
//        print_r($this->Fields);
        $this->InsertQuery->Set("deletetime", "-1");
//        echo $this->getInsertQuery()->getQueryString();
        $this->InsertQuery->Execute();
        $this->id=$this->InsertQuery->getInsertedId();
//        echo $this->getInsertQuery()->getQueryString();
        return $this->id;
    }
    private function UpdateSave()
    {
        $this->UpdateQuery=$this->getDatabase()->Update($this->getTableName());
        $FieldNames=array_keys($this->Fields);
        for($i=0;$i<count($FieldNames);$i++)
            $this->UpdateQuery->Set($FieldNames[$i],$this->Fields[$FieldNames[$i]]);
        $this->UpdateQuery->Where()->Equal("id",$this->getId());
//        echo $this->UpdateQuery->getQueryString();
        $this->UpdateQuery->Execute();
        return $this->id;
    }
    public function Save()
    {
        if($this->id!==null && $this->id>=0)
            $this->UpdateSave();
        else
            $this->InsertSave();
    }
	public function getDatabase()
	{
	    return $this->Database;
	}

	public function setDatabase($Database)
	{
	    $this->Database = $Database;
	}

	public function getTableName()
	{
	    return $this->TableName;
	}

	public function setTableName($TableName)
	{
	    $this->TableName = $TableName;
	}
}
?>