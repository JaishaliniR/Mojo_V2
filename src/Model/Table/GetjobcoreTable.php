<?php

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Datasource\ConnectionManager;

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class GetjobcoreTable extends Table {

    public function initialize(array $config) {
        $this->table('Staging_1149_Data');
        $this->primaryKey('Id');
        $this->addBehavior('Timestamp');
        $this->ModuleId = 1149;
    }

    public function findQuerypost(Query $query, array $options) {

        $connection = ConnectionManager::get('default');
        //echo "SELECT Id FROM ME_UserQuery WHERE InputEntityId='".$options['ProductionEntity']."' AND RecordStatus=1";
        $count = $connection->execute("SELECT Id FROM ME_UserQuery WHERE ProductionEntityId='" . $options['ProductionEntity'] . "' AND RecordStatus=1")->fetchAll('assoc');
        $QueryValue = str_replace("'", "''", trim($options['query']));

        if (!empty($count)) {
            $queryUpdate = "update ME_UserQuery set Query='" . $QueryValue . "'  where ProductionEntityId='" . $options['ProductionEntity'] . "' and ModuleId='" . $options['moduleId'] . "'";
            $connection->execute($queryUpdate);
        } else {
            $queryInsert = "Insert into ME_UserQuery (ProjectId,RegionId,UserID,ProductionEntityId,ModuleId,Query,QueryRaisedDate,StatusID,RecordStatus,CreatedDate,CreatedBy) values('" . $options['ProjectId'] . "','" . $options['RegionId'] . "','" . $options['user'] . "','" . $options['ProductionEntity'] . "','" . $options['moduleId'] . "','" . $QueryValue . "','" . date('Y-m-d H:i:s') . "',1,1,'" . date('Y-m-d H:i:s') . "','" . $options['user'] . "')";
            $connection->execute($queryInsert);
        }
        return $options['query'];
    }

    public function findSavedata(Query $query, array $options) {
        $user_id = $this->request->session()->read('user_id');
        $updatetempFileds = '';
        $dymamicupdatetempFileds = '';
        //pr($ProductionFields);
        foreach ($ProductionFields as $val) {
            $updatetempFileds.="[" . $val['AttributeMasterId'] . "]='" . $this->request->data[$val['AttributeMasterId']] . "',";
        }
        //  pr($DynamicFields);
        foreach ($DynamicFields as $val) {
            $dymamicupdatetempFileds.="[" . $val['AttributeMasterId'] . "]='" . $this->request->data[$val['AttributeMasterId']] . "',";
        }
        $updatetempFileds.="TimeTaken='" . $this->request->data['TimeTaken'] . "'";
        $dymamicupdatetempFileds.="TimeTaken='" . $this->request->data['TimeTaken'] . "'";
        //echo 'UPDATE Staging_1149_Data SET ' . $updatetempFileds . 'where Id=' . $productionjobId; exit;
        //echo $page;
        //echo 'UPDATE Staging_1149_Data SET ' . $updatetempFileds . 'where ProductionEntity=' . $ProductionEntity;
        $productionjob = $connection->execute('UPDATE Staging_1149_Data SET ' . $updatetempFileds . 'where ProductionEntity=' . $ProductionEntity . ' AND SequenceNumber=' . $_POST['SequenceNumber']);
        $Dynamicproductionjob = $connection->execute('UPDATE Staging_1149_Data SET ' . $dymamicupdatetempFileds . 'where ProductionEntity=' . $ProductionEntity);
    }

    public function findAjaxgroup(Query $query, array $options) {
        $key = $options['key'];
        $ProductionFields = $options['ProductionFields'];

        $emparr = array();
        $keys = array_map(function($v) use ($key, $emparr) {
            if ($v['MainGroupId'] == $key) {
                $emparr[$v['SubGroupId']] = $v;
                return $emparr;
            }
        }, $ProductionFields);
        $keysss = array_filter($keys);
        //$add = Hash::combine($keysss, '{n}.{n');
        return $keysss;
    }
    
        public function findHelptooltip(Query $query, array $options) {
        $connection = ConnectionManager::get('default');
        $Content = $connection->execute("SELECT AttributeMasterId,HelpContent FROM MC_CengageHelp WHERE ProjectId = '".$options['ProjectId']."' AND RegionId = '".$options['RegionId']."' AND AttributeMasterId = '".$options['AttributeId']."' AND RecordStatus=1")->fetchAll('assoc');
        return $Content[0]['HelpContent'];
    }
    
     function ajax_GetQcComments_seq($InputEntyId, $AttributeMasterId, $ProjectAttributeMasterId, $SequenceNumber) {
        $connection = ConnectionManager::get('default');

        $cmdOldData = $connection->execute("select mvc.QCComments,mvc.StatusID, mve.ErrorCategoryName from MV_QC_Comments as mvc inner join MV_QC_ErrorCategoryMaster as mve on mvc.ErrorCategoryMasterId = mve.Id where mvc.AttributeMasterId = $AttributeMasterId and mvc.ProjectAttributeMasterId=$ProjectAttributeMasterId and mvc.InputEntityId=$InputEntyId and mvc.StatusID IN (1,2,3) order by mvc.SequenceNumber")->fetchAll('assoc');
        if(!empty($cmdOldData)){
      // $cmdOldData = array_column($cmdOldData, 'QCComments');
       $i=1;
       foreach($cmdOldData as $key => $val) {
            $new_cmdOldData['QCComments'][$i]=$val['QCComments'];
            $new_cmdOldData['ErrorCategoryName'][$i]=$val['ErrorCategoryName'];
            $new_cmdOldData['StatusID'][$i]=$val['StatusID'];
            $i++;
        }
       
    }
     return $new_cmdOldData;
    }

}
