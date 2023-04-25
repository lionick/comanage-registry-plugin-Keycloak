<?php

/**
 * COmanage Registry CO Keycloak Provisioner Target Model
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

//App::import('Model', 'ConnectionManager');
App::uses("CoProvisionerPluginTarget", "Model");
App::uses("Keycloak", "Model");
App::uses('Security', 'Utility');
App::uses('Hash', 'Utility');

/**
 * Class KeycloakProvisionerTarget
 */
class CoKeycloakProvisionerTarget extends CoProvisionerPluginTarget
{
  // XXX All the classes/models that have tables should start with CO for the case of provisioners
  // Define class name for cake
  public $name = "CoKeycloakProvisionerTarget";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array('CoProvisioningTarget');

  // Default display field for cake generated views
  public $displayField = "vo";


  /**
   * Actions to take before a save operation is executed.
   *
   * @since  COmanage Registry v3.1.0
   */

  public function beforeSave($options = array())
  {
    $this->log(__METHOD__ . "::@Test", LOG_DEBUG);
    //remove new lines and whitespaces for "VO Whitelist" field
    if (isset($this->data['CoKeycloakProvisionerTarget']['vo_whitelist'])) {
      $this->data['CoKeycloakProvisionerTarget']['vo_whitelist'] = str_replace(array("\r", "\n"), '', $this->data['CoKeycloakProvisionerTarget']['vo_whitelist']);
      $values = explode(',', $this->data['CoKeycloakProvisionerTarget']['vo_whitelist']);
      foreach ($values as $key => $value) {
        $values[$key] = trim($value);
      }
      $this->data['CoKeycloakProvisionerTarget']['vo_whitelist'] = implode(',', $values);
    }
    if (isset($this->data['CoKeycloakProvisionerTarget']['api_client_secret'])) {
      $key = Configure::read('Security.salt');
      Configure::write('Security.useOpenSsl', true);
      $password = base64_encode(Security::encrypt($this->data['CoKeycloakProvisionerTarget']['api_client_secret'], $key));
      $this->data['CoKeycloakProvisionerTarget']['api_client_secret'] = $password;
    }
  }

  public function getConfiguration($coId)
  {
    $this->log(__METHOD__ . "::@Test", LOG_DEBUG);
    $args = array();
    $args['joins'] = array(
      array(
        'table' => 'cm_co_provisioning_targets',
        'alias' => 'co_provisioning_targets',
        'type' => 'INNER',
        'conditions' => array(
          'CoKeycloakProvisionerTarget.co_provisioning_target_id = co_provisioning_targets.id'
        )
      )
    );
    $args['conditions']['co_provisioning_targets.co_id'] = $coId;
    $args['conditions']['co_provisioning_targets.plugin'] = 'KeycloakProvisioner';

    $keycloakProvisioners = $this->find('all', $args);

    //Return only the first result. What if we have more than one?? Is it possible?
    return $keycloakProvisioners[0]['CoKeycloakProvisionerTarget'];
  }

  // Validation rules for table elements
  public $validate = array(
    'co_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO PROVISIONING TARGET ID must be provided'
    ),
    'api_base_url' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'api_realm' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'api_client_id' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'api_client_secret' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'enable_vo_whitelist' => array(
      'rule' => array('boolean')
    ),
    'entitlement_format_include_vowht' => array(
      'rule' => array('boolean')
    ),
    'rciam_external_entitlements' => array(
      'rule' => array('boolean')
    ),
    'vo_whitelist' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'vo_roles' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'merge_entitlements' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'urn_namespace' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'urn_authority' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'urn_legacy' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'vo_group_prefix' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'entitlement_format' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'identifier_type' => array(
      'rule' => array(
        'inList',
        array(
          KeycloakProvisionerIdentifierEnum::Badge,
          KeycloakProvisionerIdentifierEnum::Enterprise,
          KeycloakProvisionerIdentifierEnum::ePPN,
          KeycloakProvisionerIdentifierEnum::ePTID,
          KeycloakProvisionerIdentifierEnum::ePUID,
          KeycloakProvisionerIdentifierEnum::Mail,
          KeycloakProvisionerIdentifierEnum::National,
          KeycloakProvisionerIdentifierEnum::Network,
          KeycloakProvisionerIdentifierEnum::OpenID,
          KeycloakProvisionerIdentifierEnum::ORCID,
          KeycloakProvisionerIdentifierEnum::ProvisioningTarget,
          KeycloakProvisionerIdentifierEnum::Reference,
          KeycloakProvisionerIdentifierEnum::SORID,
          KeycloakProvisionerIdentifierEnum::UID
        )
      ),
      'required' => true
    ),
  );

  /**
   * checkRequest
   *
   * @param  mixed $op
   * @param  mixed $provisioningData
   * @param  mixed $data
   * @return void
   */
  public function checkRequest($op, $provisioningData,  $data)
  {
    $this->log(__METHOD__ . "::@Test", LOG_DEBUG);
    // Check if its a request we want to provision
    if (!empty($_REQUEST['_method']) && $_REQUEST['_method'] == 'PUT' && !empty($_REQUEST['data']['CoPersonRole']) && $_REQUEST['data']['CoPersonRole']['status'] == 'S' && !empty($data['co_person_id'])) { //SUSPEND
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoPersonRole Form] Suspended User with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if ((!empty($_REQUEST['_method']) && ($_REQUEST['_method'] == 'PUT' || $_REQUEST['_method'] == 'POST')) && !empty($_REQUEST['data']['CoPersonRole']) && $_REQUEST['data']['CoPersonRole']['status'] == 'A' && !empty($data['co_person_id'])) { //ACTIVE
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoPersonRole Form] Active User with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST['_method']) && $_REQUEST['_method'] == 'PUT' && !empty($_REQUEST['data']['CoPersonRole']) && !empty($data['co_person_id'])) { //Another Action of Co Person Role
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoPersonRole Form] Action for User with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_person_roles/delete/') !== FALSE && !empty($data['co_person_id'])) { //delete co person role
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [Co Person Roles] delete role from user with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_group_members/delete/') !== FALSE && !empty($data['co_person_id'])) { //delete co group member
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoGroupMember] delete from group, user with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_group_members/add_json') !== FALSE && !empty($data['co_person_id'])) { //add co group member from rest api
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoGroupMember] REST API CALL: add group to user with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_groups/add') !== FALSE && !empty($data['co_person_id'])) { //add group
      /* $data['co_person_identifier'] = $provisioningData['CoPerson']['actor_identifier'];
        $CoPerson = ClassRegistry::init('CoPerson');
        $data['co_person_id'] = $CoPerson->field('id', array('actor_identifier' => $data['co_person_identifier']));
        $data['co_group_id'] = $provisioningData['CoGroup']['id'];
        $data['co_id'] = $provisioningData['CoGroup']['co_id'];*/
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoGroup] add group membership to user id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_groups/delete') !== FALSE) { //delete co group
      $data['co_group_id'] = explode('/', array_keys($_REQUEST)[0])[3];
      $CoGroup = ClassRegistry::init('CoGroup');
      $data['group_name'] = $CoGroup->field('name', array('id' => $data['co_group_id']));
      $data['co_id'] = $CoGroup->field('co_id', array('id' => $data['co_group_id']));
      $data['delete_group'] = TRUE;
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoGroup] Delete Group with id:' . $data['co_group_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_groups/edit') !== FALSE) {
      $data['co_group_id'] = explode('/', array_keys($_REQUEST)[0])[3];
      $CoGroup = ClassRegistry::init('CoGroup');
      $data['group_name'] = $CoGroup->query('SELECT name as group_name, co_id FROM cm_co_groups WHERE co_group_id=' . $data['co_group_id'] . '  AND revision = (SELECT MAX(revision) FROM cm_co_groups g2 WHERE g2.co_group_id=' . $data['co_group_id'] . ');')[0][0]['group_name'];
      $data['new_group_name'] = $_REQUEST['data']['CoGroup']['name'];
      if ($data['group_name'] != $data['new_group_name']) {
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoGroup] Rename Group with id:' . $data['co_group_id'], LOG_DEBUG);
        $data['rename_group'] = TRUE;
      }
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/cous/edit') !== FALSE) {
      $data['new_cou']['cou_id'] = explode('/', array_keys($_REQUEST)[0])[3];
      $Cou = ClassRegistry::init('Cou');
      $data['cou'] = $Cou->query('SELECT name as group_name, id as cou_id FROM cm_cous WHERE cou_id=' . $data['new_cou']['cou_id'] . '  AND revision = (SELECT MAX(revision) FROM cm_cous c2 WHERE c2.cou_id=' . $data['new_cou']['cou_id'] . ');')[0][0]; //we need the previous name
      $data['new_cou']['group_name'] = $_REQUEST['data']['Cou']['name'];
      $data['rename_cou'] = TRUE;
      if ($data['new_cou']['group_name']  != $data['cou']['group_name']) {
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [Cou] Rename Cou with id:' . $data['new_cou']['cou_id'], LOG_DEBUG);
      } else { // parent_id changed -> see checkWriteFollowups at CousController
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [Cou] Parent Changed for  Cou with id:' . $data['new_cou']['cou_id'], LOG_DEBUG);
      }
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/cous/delete') !== FALSE) { //delete co group
      $data['cou']['cou_id'] = explode('/', array_keys($_REQUEST)[0])[3];
      $Cou = ClassRegistry::init('Cou');
      $data['cou']['group_name'] = $Cou->field('name', array('id' => $data['cou']['cou_id']));
      $data['delete_cou'] = TRUE;
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [Cou] Delete Cou with id:' . $data['cou']['cou_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_group_members') !== FALSE && !empty($data['co_person_id'])) { //co group member action
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [CoGroupMember Action] for user with id:' . $data['co_person_id'], LOG_DEBUG);
    }
    //co_person_roles_json when remove role 
    //co_person_roles/250_json when revoke role from admin
    else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_person_roles') !== FALSE && !empty($data['co_person_id'])) { //co group member action
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => [Co Person Roles Action] for user with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST['_method']) && $_REQUEST['_method'] == 'POST' && !empty($_REQUEST['data']['CoPerson']) && $_REQUEST['data']['CoPerson']['confirm'] == '1' && isset($_REQUEST['/co_people/expunge/' . $data['co_person_id']])) { //DELETE
      $data['user_deleted'] = TRUE;
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => Delete User with id:' . $data['co_person_id'], LOG_DEBUG);
    } else if (!empty($_REQUEST) && strpos(array_keys($_REQUEST)[0], '/co_provisioning_targets') !== FALSE && !empty($data['co_person_id'])) { //Manually Provision user
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => Manually Provision User with id:' . $data['co_person_id'], LOG_DEBUG);
    } else {
      return NULL;
    }
    return $data;
  }

  /**
   * Provision for the specified CO Person.
   *
   * @param Array CO Provisioning Target data
   * @param ProvisioningActionEnum Registry transaction type triggering provisioning
   * @param Array Provisioning data, populated with ['CoPerson'] or ['CoGroup']
   * @return Boolean True on success
   * @throws RuntimeException
   * @since  COmanage Registry v0.8
   */

  public function provision($coProvisioningTargetData, $op, $provisioningData)
  {
    $this->log(__METHOD__ . "::@Test", LOG_DEBUG);
    $this->log(__METHOD__ . "::action => " . $op, LOG_DEBUG);
    $data = NULL;

    switch ($op) {
      case ProvisioningActionEnum::CoPersonAdded:
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        $data['co_id'] = $provisioningData['Co']['id'];
        $data['co_person_identifier'] = $provisioningData['CoPerson']['actor_identifier'];
        $data['co_person_id'] = $provisioningData['CoPerson']['id'];
        if (!empty($provisioningData['Identifier'])) {
          $data['co_person_identifier'] = Hash::extract($provisioningData['Identifier'], '{n}[type=' . $coProvisioningTargetData['CoKeycloakProvisionerTarget']['identifier_type'] . '].identifier')[0];
        }
        break;
      case ProvisioningActionEnum::CoPersonUpdated:
      case ProvisioningActionEnum::CoPersonReprovisionRequested:
        $data['co_id'] = $provisioningData['Co']['id'];
        $data['co_person_identifier'] = $provisioningData['CoPerson']['actor_identifier'];
        $data['co_person_id'] = $provisioningData['CoPerson']['id'];
        if (!empty($provisioningData['Identifier'])) {
          $data['co_person_identifier'] = Hash::extract($provisioningData['Identifier'], '{n}[type=' . $coProvisioningTargetData['CoKeycloakProvisionerTarget']['identifier_type'] . '].identifier')[0];
        }
        break;
      case ProvisioningActionEnum::CoPersonExpired:
        break;
      case ProvisioningActionEnum::CoPersonPetitionProvisioned:
        break;
      case ProvisioningActionEnum::CoGroupUpdated:
        $data['co_id'] = $provisioningData['CoGroup']['co_id'];
        //$co_person_identifier = $provisioningData['CoGroup']['CoPerson']['actor_identifier'];
        $data['co_person_id'] = $provisioningData['CoGroup']['CoPerson']['id'];
        $identifier = ClassRegistry::init('Identifier');
        $data['co_person_identifier'] = $identifier->field('identifier', array('co_person_id' => $data['co_person_id'], 'type' => $coProvisioningTargetData['CoKeycloakProvisionerTarget']['identifier_type']));
        break;
      case ProvisioningActionEnum::CoGroupDeleted:
        break;
      default:
        // Ignore all other actions
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' not allowed/implemented', LOG_DEBUG);
        return true;
    }
    //$this->log(__METHOD__ . 'Request' . print_r($_REQUEST, true), LOG_DEBUG);

    $data = $this->checkRequest($op, $provisioningData, $data);

    if (empty($data))
      return;

    // Construct users profile
    $user_profile = $this->retrieveUserCouRelatedStatus($provisioningData, $coProvisioningTargetData);
    $access_token = $this->retrieveAccessToken($coProvisioningTargetData);
    $this->log(__METHOD__ . '::Access Token from Keycloak ' . $access_token, LOG_DEBUG);
    
    // Set configuration to keycloak variable
    $keycloak = ClassRegistry::init('KeycloakUsers');
    Keycloak::config($keycloak, $coProvisioningTargetData['CoKeycloakProvisionerTarget'], $user_profile, $access_token);
    //$users = $this->retrieveUsersByEduPersonEntitlement($keycloak, "urn:mace:egi.eu:group:vo.example.org:");
    if (!empty($data['group_name']) && !empty($data['delete_group'])) { //group Deleted
      //Delete All Entitlements For this Group
      Keycloak::deleteEntitlementsByGroup($this,
        $keycloak,
        $data['group_name'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_namespace'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_legacy'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_authority'],
        SyncEntitlements::get_vo_group_prefix(
          $coProvisioningTargetData['CoKeycloakProvisionerTarget']['vo_group_prefix'],
          $data['co_id']
        )
      );
    } else if (!empty($data['rename_group'])) { //group Renamed
      // Rename All Entitlements For this Group 
      Keycloak::renameEntitlementsByGroup($this,
        $keycloak,
        $data['group_name'],
        $data['new_group_name'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_namespace'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_legacy'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_authority'],
        SyncEntitlements::get_vo_group_prefix($coProvisioningTargetData['CoKeycloakProvisionerTarget']['vo_group_prefix'], $provisioningData['CoGroup']['co_id'])
      );
    } else if (!empty($data['rename_cou'])) { //cou Renamed
      // Rename All Entitlements For this Cou
      $paths = SyncEntitlements::getCouTreeStructure(array($data['cou']));
      $old_group = ((empty($paths) || empty($paths[$data['cou']['cou_id']])) ? urlencode($data['cou']['group_name']) : $paths[$data['cou']['cou_id']]['path']);
      $paths = SyncEntitlements::getCouTreeStructure(array($data['new_cou']));
      $new_group = ((empty($paths) || empty($paths[$data['new_cou']['cou_id']])) ? urlencode($data['new_cou']['group_name']) : $paths[$data['new_cou']['cou_id']]['path']);
      Keycloak::renameEntitlementsByCou($this,
        $keycloak,
        $old_group,
        $new_group,
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_namespace'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_legacy'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_authority']
      );
    } else if (!empty($data['delete_group'])) { //group Deleted
      // Delete All Entitlements For this Group
      Keycloak::deleteEntitlementsByGroup($this,
        $keycloak,
        $data['group_name'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_namespace'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_legacy'],
        $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_authority'],
        SyncEntitlements::get_vo_group_prefix($coProvisioningTargetData['CoKeycloakProvisionerTarget']['vo_group_prefix'], $data['co_id'])
      );
    }
    // Is needed for :admins group
    else if (!empty($data['delete_cou'])) { //cou Deleted
      // Delete All Entitlements For this Cou
      $paths = SyncEntitlements::getCouTreeStructure(array($data['cou']));
      $cou_name = null;
      if (!empty($paths) && !empty($data['cou']['cou_id'])) {
        $cou_name = $paths[$data['cou']['cou_id']]['path'];
      } else {
        $Cou = ClassRegistry::init('Cou');
        $Cou->id = $data['cou']['cou_id'];
        $cou_name = $Cou->field('name');
      }
      if (!is_null($cou_name)) {
        Keycloak::deleteEntitlementsByCou(
          $this,
          $keycloak,
          $cou_name,
          $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_namespace'],
          $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_legacy'],
          $coProvisioningTargetData['CoKeycloakProvisionerTarget']['urn_authority']
        );
      }
    } else {
      //Get Person by the epuid
      //$person = $keycloak->find('all', array('conditions'=> array('KeycloakUsers.sub' => $data['co_person_identifier'])));

      $person = $this->retrievePerson($keycloak, $data['co_person_identifier']);

      if (empty($person[0])) {
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => person id not found in keycloak with identifier: ' . $data['co_person_identifier'], LOG_DEBUG);
        return false;
      }
      $this->log(__METHOD__ . '::Provisioning action PERSON ' . $op . ' =>' . var_export($person[0], true), LOG_DEBUG);

      //Get User Entitlements From Keycloak
      $keycloak_entitlements = $person[0]->attributes->eduPersonEntitlement;
      $this->log(__METHOD__ . '::Provisioning action ' . $op . ' =>' . var_export($person[0]->attributes->eduPersonEntitlement, true), LOG_DEBUG);

      Keycloak::setKeycloakEntitlements($keycloak, $keycloak_entitlements);

      //Keycloak::config($keycloak_entitlements, $datasource, 'user_edu_person_entitlement', $coProvisioningTargetData['CoKeycloakProvisionerTarget'], $user_profile);
      if (!empty($data['user_deleted'])) {
        Keycloak::deleteAllEntitlements($keycloak_entitlements);
      } else {
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => current_entitlements from Keycloak:' . var_export($person[0]->attributes->eduPersonEntitlement, true), LOG_DEBUG);

        //$current_entitlements = Keycloak::getCurrentEntitlements($keycloak_entitlements, $person[0]['KeycloakUsers']['id']);
        //$this->log(__METHOD__ . '::Provisioning action ' . $op . ' => current_entitlements from Keycloak: ' . print_r($current_entitlements, true), LOG_DEBUG);
        //Get New Entitlements From Comanage
        $syncEntitlements = new SyncEntitlements($coProvisioningTargetData['CoKeycloakProvisionerTarget'], $data['co_id']);
        $new_entitlements = $syncEntitlements->getEntitlements($data['co_person_id']);
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' => new_entitlements from comanage: ' . print_r($new_entitlements, true), LOG_DEBUG);
        //$this->log(__METHOD__ . '::Provisioning action ' . $op . ' => keycloak: ' . print_r($keycloak, true), LOG_DEBUG);

        //Delete Old Entitlements
        Keycloak::deleteOldEntitlements($keycloak, $keycloak_entitlements, $new_entitlements);

        //Insert New Entitlements
        Keycloak::insertNewEntitlements($keycloak, $keycloak_entitlements, $new_entitlements);
        
        //TODO: Uncomment for OPENAIRE BETA
        //$this->updatePersonEntitlements($keycloak, $person[0]);
        return;
      }
    }
  }

  /**
   * Retrieve AccessToken From Keycloak
   *
   * @return void
   */
  protected function retrieveAccessToken($coProvisioningTargetData)
  {
    $client = new GuzzleHttp\Client();

    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
    ];
    Configure::write('Security.useOpenSsl', true);
    $data = [
      'grant_type' => 'client_credentials',
      'client_id' => $coProvisioningTargetData['CoKeycloakProvisionerTarget']['api_client_id'], // Replace with your client ID
      'client_secret' => Security::decrypt(base64_decode($coProvisioningTargetData['CoKeycloakProvisionerTarget']['api_client_secret']), Configure::read('Security.salt')), // Replace with your client secret
    ];

    $url = $coProvisioningTargetData['CoKeycloakProvisionerTarget']['api_base_url'] . '/realms/' . $coProvisioningTargetData['CoKeycloakProvisionerTarget']['api_realm']  . '/protocol/openid-connect/token';
    $response = $client->post($url, [
      'headers' => $headers,
      'form_params' => $data
    ]);

    $body = json_decode($response->getBody());
    return $body->access_token;
  }

  /**
   * Retrieve users By eduPersonEntitlement From Keycloak
   *
   * @return void
   */
  public function retrieveUsersByEduPersonEntitlement($keycloak, $eduPersonEntitlement)
  {
    $client = new GuzzleHttp\Client();
    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
      'Authorization' => 'Bearer ' . $keycloak->accessToken
    ];

    $body = [];
    $first = 0;
    
    do {
      $url = $keycloak->apiBaseUrl
      . '/admin/realms/'
      . $keycloak->apiRealm
      . '/users?q=eduPersonEntitlement:' . $eduPersonEntitlement
      . '&first=' . $first.'&exact=true';

      $response = $client->get($url, [
        'headers' => $headers,
      ]);
      $part_response = json_decode($response->getBody());
      if(!empty($part_response)) {
        $body = array_merge($body, $part_response);
      }
      $first += 100;
      $this->log(__METHOD__ . " ::  url " . $url, LOG_DEBUG);
      $this->log(__METHOD__ . " ::  body " . var_export($body, true), LOG_DEBUG);

    } while (!empty($part_response));


    return $body;
  }

  /**
   * Retrieve Person From Keycloak
   *
   * @return void
   */
  protected function retrievePerson($keycloak, $person_id)
  {
    $client = new GuzzleHttp\Client();
    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
      'Authorization' => 'Bearer ' . $keycloak->accessToken
    ];

    $url = $keycloak->apiBaseUrl . '/admin/realms/' .  $keycloak->apiRealm . '/users?username=' . $person_id;

    $response = $client->get($url, [
      'headers' => $headers,
    ]);

    $body = json_decode($response->getBody());

    return $body;
  }

  protected function updatePersonEntitlements($keycloak, $keycloak_user)
  {
    try {
      $client = new GuzzleHttp\Client();

      $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $keycloak->accessToken
      ];
      $this->log( __METHOD__ . ':: entitlements before update ' . var_export($keycloak_user->attributes->eduPersonEntitlement, true), LOG_DEBUG);
      $this->log( __METHOD__ . ':: entitlements after update will be ' . var_export($keycloak->entitlements, true), LOG_DEBUG);
      
      $keycloak_user->attributes->eduPersonEntitlement = $keycloak->entitlements;
      $url = $keycloak->apiBaseUrl . '/admin/realms/' . $keycloak->apiRealm . '/users/' . $keycloak_user->id;
      $response = $client->put($url, [
        'headers' => $headers,
        'json' => [
          'attributes' => $keycloak_user->attributes
        ],
      ]);


      $status = $response->getStatusCode();

      // Handle the response as needed
      if ($status === 204) {
        // Successful PUT request
        $this->log(__METHOD__ . " ::  user updated successfully", LOG_DEBUG);
      } else {
        // Handle error
        $this->log(__METHOD__ . " ::  error while updating the user status: " . $status, LOG_ERROR);
      }
    } catch (GuzzleHttp\Exception\RequestException $e) {
      // Handle request exception
      $this->log(__METHOD__ . " ::  error while updating the user " .  $e->getMessage(), LOG_ERROR);
    } catch (GuzzleHttp\Exception\GuzzleException $e) {
      // Handle Guzzle exception
      $this->log(__METHOD__ . " ::  error while updating the user " .  $e->getMessage(), LOG_ERROR);
    } catch (Exception $e) {
      // Handle other exceptions
      $this->log(__METHOD__ . " ::  error while updating the user " .  $e->getMessage(), LOG_ERROR);
    }
    return $status;
  }

  /**
   * CO Person profile based on COU and Group ID. The profile is constructed based on OrgIdentities linked to COPerson.
   *
   * @param array $provisioningData
   * @param array $coProvisioningTargetData
   * @return array
   */
  protected function retrieveUserCouRelatedStatus($provisioningData, $coProvisioningTargetData)
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $args = array();
    $args['conditions']['CoProvisioningTarget.id'] = $coProvisioningTargetData["CoKeycloakProvisionerTarget"]["co_provisioning_target_id"];
    $args['fields'] = array('provision_co_group_id');
    $args['contain'] = false;
    $provision_group_ret = $this->CoProvisioningTarget->find('first', $args);
    $co_group_id = $provision_group_ret["CoProvisioningTarget"]["provision_co_group_id"];

    $user_memberships_profile = !is_array($provisioningData['CoGroupMember']) ? array()
      : Hash::flatten($provisioningData['CoGroupMember']);

    $in_group = array_search($co_group_id, $user_memberships_profile, true);

    if (!empty($in_group)) {
      $index = explode('.', $in_group, 2)[0];
      $user_membership_status = $provisioningData['CoGroupMember'][$index];
      // XXX Do not set the cou_id unless you are certain of its value
      $cou_id = !empty($user_membership_status["CoGroup"]["cou_id"]) ? $user_membership_status["CoGroup"]["cou_id"] : null;
    }

    // Create the profile of the user according to the group_id and cou_id of the provisioned
    // resources that we configured
    // XXX i can not let COmanage treat $cou_id = null as ok since i allow Null COUs. This means that
    // XXX we will get back the default CO Role, which will be the wrong one.
    $args = array();
    $args['conditions']['CoPerson.id'] = $provisioningData["CoPerson"]["id"];
    if (isset($cou_id)) {
      $args['contain']['CoPersonRole'] = array(
        'conditions' => ['CoPersonRole.cou_id' => $cou_id],  // XXX Be carefull with the null COUs
      );
    }
    $args['contain']['CoGroupMember'] = array(
      'conditions' => ['CoGroupMember.co_group_id' => $co_group_id],
    );
    $args['contain']['CoGroupMember']['CoGroup'] = array(
      'conditions' => ['CoGroup.id' => $co_group_id],
    );
    // todo: Check if the Cert is linked under OrgIdentity or CO Person
    $args['contain']['CoOrgIdentityLink']['OrgIdentity'] = array(
      'Assurance',                                                // Include Assurances
      'Cert',                                                     // Include any Certificate
      //      'Cert' => array(                                            // Include Certificates
      //        'conditions' => ['Cert.issuer is not null'],
      //      ),
    );

    // XXX Filter with this $user_profile["CoOrgIdentityLink"][2]["OrgIdentity"]['Cert']
    // XXX We can not perform any action with VOMS without a Certificate having both a subjectDN and an Issuer
    // XXX Keep in depth level 1 only the non empty Certificates
    $user_profile = $this->CoProvisioningTarget->Co->CoPerson->find('first', $args);
    if(!empty($user_profile["CoOrgIdentityLink"])) {
      foreach ($user_profile["CoOrgIdentityLink"] as $link) {
        if (!empty($link["OrgIdentity"]['Cert'])) {
          foreach ($link["OrgIdentity"]['Cert'] as $cert) {
            $user_profile['Cert'][] = $cert;
          }
        }
      }
    }
    // Fetch the orgidentities linked with the certificates
    if (!empty($user_profile['Cert'])) {
      // Extract the Certificate ids
      // todo: Check if the Model is linked to CO Person, OrgIdentity or Both
      $cert_ids = Hash::extract($user_profile['Cert'], '{n}.id');
      $args = array();
      $args['conditions']['Cert.id'] = $cert_ids;
      $args['contain'] = array('OrgIdentity');
      $args['contain']['OrgIdentity'][] = 'TelephoneNumber';
      $args['contain']['OrgIdentity'][] = 'Address';
      $args['contain']['OrgIdentity'][] = 'Assurance';
      $args['contain']['OrgIdentity'][] = 'Identifier';
      $this->Cert = ClassRegistry::init('Cert');
      $user_profile['Cert'] = $this->Cert->find('all', $args);
    }

    return $user_profile;
  }
}
