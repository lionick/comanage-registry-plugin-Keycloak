<?php

class AppSchema extends CakeSchema {

  public $connection = 'default';

  public function before($event = array())
  {
    
    return true;
  }

  public function after($event = array())
  {
    if (isset($event['create'])) {
      switch ($event['create']) {
        case 'co_keycloak_provisioner_targets':
          $KeycloakProvisioner = ClassRegistry::init('KeycloakProvisioner.CoKeycloakProvisionerTarget');
          $KeycloakProvisioner->useDbConfig = $this->connection;
          // Add the constraints or any other initializations
          $KeycloakProvisioner->query("ALTER TABLE ONLY public.cm_co_keycloak_provisioner_targets ADD CONSTRAINT cm_co_keycloak_provisioner_targets_co_provisioning_target_id_fkey FOREIGN KEY (co_provisioning_target_id) REFERENCES public.cm_co_provisioning_targets(id)");
          break;
      }
    }
  }

  public $co_keycloak_provisioner_targets = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
    'co_provisioning_target_id' => array('type' => 'integer', 'null' => false, 'length' => 10),
    
    'api_base_url' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512),

    'api_client_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'api_client_secret' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),

    'encoding' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'vo_roles' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),
    'merge_entitlements' => array('type' => 'boolean', 'null' => true, 'default' => null),
    'urn_namespace' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'urn_authority' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'urn_legacy' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'enable_vo_whitelist' => array('type' => 'boolean', 'null' => true, 'default' => null),
    'vo_whitelist' => array('type' => 'text', 'null' => true, 'default' => null, 'length' => 4000),
    'vo_group_prefix' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 4000),
    'entitlement_format' => array('type' => 'text', 'null' => true, 'default' => null, 'length' => 4000),
    'entitlement_format_include_vowht' => array('type' => 'boolean', 'null' => true, 'default' => null),
    'rciam_external_entitlements' => array('type' => 'boolean', 'null' => true, 'default' => null),
    'identifier_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
    'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
    'deleted' => array('type' => 'boolean', 'null' => false, 'default' => false),
    'indexes' => array(
      'PRIMARY' => array('unique' => true, 'column' => 'id')
    ),
    'tableParameters' => array()
  );

}