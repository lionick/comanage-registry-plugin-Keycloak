<?php
App::uses('CakeLog', 'Log');
/**
 * This class is quering the Keycloak Connect Database
 *
 * 
 */
class Keycloak
{

  /**
   * config
   *
   * @param  mixed $keycloak
   * @param  array $coProvisioningTargetData
   * @return void
   */
  public static function config($keycloak, $coProvisioningTargetData = NULL, $user_profile = NULL, $access_token)
  {

    $keycloak->userProfile = $user_profile;
    $keycloak->accessToken = $access_token;
    if (!is_null($coProvisioningTargetData)) {
      foreach ($coProvisioningTargetData as $key => $value) {
        if (!in_array($key, array('id', 'deleted', 'created', 'modified', 'co_provisioning_target_id'))) {
          $key = lcfirst(Inflector::camelize($key));
          $keycloak->$key = $value;
        }
      }
    }
  }

  // public static function setKeycloakEntitlements($keycloak, $keycloakEntitlements)
  // {
  //   $keycloak->entitlements = $keycloakEntitlements;
  // }

public static function updateEntitlements($provisioner, $keycloak, $person, $new_entitlements) {
  
  //Delete Old Entitlements
  Keycloak::deleteOldEntitlements($keycloak, $person, $new_entitlements);

  //Insert New Entitlements
  Keycloak::insertNewEntitlements($keycloak, $person, $new_entitlements);
          
  //TODO: Uncomment for OPENAIRE BETA
  $provisioner->updatePersonEntitlements($keycloak, $person);
}
  /**
   * deleteOldEntitlements
   *
   * @param  mixed $keycloak
   * @param  integer $user_id
   * @param  array $current_entitlements
   * @param  array $new_entitlements
   * @return void
   */
  public static function deleteOldEntitlements($keycloak, &$person, $new_entitlements)
  {
    $deleteEntitlements_white = array();
    $deleteEntitlements_format = array();
    $current_entitlements = $person->attributes->eduPersonEntitlement;
    // Find the candidate Entitlements
    $deleteEntitlements = array_diff($current_entitlements, $new_entitlements);
    CakeLog::write('debug', __METHOD__ . ':: delete old entitlements ', LOG_DEBUG);
    // There is nothing to delete
    if (empty($deleteEntitlements)) {
      CakeLog::write('debug', __METHOD__ . ':: no entitlements to delete ', LOG_DEBUG);
      return;
    }
    //Remove the ones matching the Entitlement Format regex
    if (!empty($keycloak->entitlementFormat)) {
      $deleteEntitlements_format  = preg_grep($keycloak->entitlementFormat, $deleteEntitlements);
    }
    // Remove/Delete the ones constructed from the VO Whitelist
    if (
      $keycloak->entitlementFormatIncludeVowht
      && !empty($keycloak->voWhitelist)
    ) {
      $vowhite_list = explode(",", $keycloak->voWhitelist);
      // Get all COU children of the the Whitelisted VOs and append
      $vowhite_list_children = Keycloak::getVoWitelistChildren($vowhite_list);
      $vowhite_list = array_merge($vowhite_list, $vowhite_list_children);
      foreach ($vowhite_list as $vo_name) {
        // Handle only the cou groups. Not the admin groups entitlements
        $whitelist_regex = "/" . $keycloak->urnNamespace . ":group:" . $vo_name . ":(.*)#" . $keycloak->urnAuthority . "/i";
        $deleteEntitlements_tmp  = preg_grep($whitelist_regex, $deleteEntitlements);
        $deleteEntitlements_white = array_merge($deleteEntitlements_white, $deleteEntitlements_tmp);
      }
    }
    // Calculate the final list of entitlements to be deleted
    $deleteEntitlements = array_merge($deleteEntitlements_white, $deleteEntitlements_format);
    if (!empty($deleteEntitlements)) {
      CakeLog::write('debug', __METHOD__ . ':: entitlements to be deleted at Keycloak ' . var_export($deleteEntitlements, true), LOG_DEBUG);
      $person->attributes->eduPersonEntitlement = array_values(array_diff($person->attributes->eduPersonEntitlement, $deleteEntitlements));
      CakeLog::write('debug', __METHOD__ . ':: entitlements remained ' . var_export($person->attributes->eduPersonEntitlement, true), LOG_DEBUG);
    } else {
      CakeLog::write('debug', __METHOD__ . ':: no entitlements to be deleted at Keycloak ', LOG_DEBUG);
    }
  }

  /**
   * deleteEntitlementsByCou
   *
   * @param  mixed $keycloak
   * @param  mixed $old_group_name
   * @param  mixed $new_group_name
   * @param  mixed $urn_namespace
   * @param  mixed $urn_legacy
   * @param  mixed $urn_authority
   * @param  mixed $vo_group_prefix
   * @return void
   */
  public static function deleteEntitlementsByCou($provisioner, $keycloak, $cou_name,  $urn_namespace, $urn_legacy, $urn_authority)
  {
    if (
      !empty($keycloak->entitlementFormat)
      && strpos($keycloak->entitlementFormat, "/") == 0
    ) {
      $regex = explode('/', $keycloak->entitlementFormat)[1];
    } else {
      $regex = $keycloak->entitlementFormat;
    }

    $group = !empty($group_name) ? ":" . $group_name : "";
    // cou_names are already url_encoded
    $entitlement_regex = '^' . $urn_namespace . ":group:" . str_replace('+', '\+', $cou_name) . $group . ":(.*)#" . $urn_authority;
    // keycloak doesnt support regex so we must search for something that is compatible with "LIKE" query
    $entitlement_keycloak = $urn_namespace . ":group:" . str_replace('+', '\+', $cou_name) . $group;
    if ($urn_legacy) {
      $entitlement_regex = '(' . $entitlement_regex . ')|(^' . $urn_namespace . ":group:" . str_replace('+', '\+', $cou_name) . '#' . $urn_authority . ')';
    }

    $users = $provisioner->retrieveUsersByEduPersonEntitlement($keycloak, $entitlement_keycloak);
    foreach ($users as $keycloak_user) {
      // Loop through the entitlements array and remove matching entitlements
      foreach ($keycloak_user->attributes->eduPersonEntitlement as $key => $entitlement) {
        if (preg_match('/' . $entitlement_regex . '/', $entitlement) && preg_match('/' . $regex . '/', $entitlement)) {

          unset($keycloak_user->attributes->eduPersonEntitlement[$key]);
        }
        
      }
      CakeLog::write('debug', __METHOD__ . ':: entitlements left: ' . $keycloak_user->attributes->eduPersonEntitlement, LOG_DEBUG);
      // TODO Remove comment
      //$provisioner->updatePersonEntitlements($keycloak, $keycloak_user);
    }
  }

  /**
   * deleteEntitlementsByGroup
   *
   * @param  mixed $keycloak
   * @param  mixed $group_name
   * @param  mixed $urn_namespace
   * @param  mixed $urn_legacy
   * @param  mixed $urn_authority
   * @param  mixed $vo_group_prefix
   * @return void
   */
  public static function deleteEntitlementsByGroup($provisioner, $keycloak, $group_name, $urn_namespace, $urn_legacy, $urn_authority, $vo_group_prefix)
  {
    if (
      !empty($keycloak->entitlementFormat)
      && strpos($keycloak->entitlementFormat, "/") === 0
    ) {
      $regex = explode('/', $keycloak->entitlementFormat)[1];
    } else {
      $regex = $keycloak->entitlementFormat;
    }

    $entitlement_regex = '^' . $urn_namespace . ':group:' . $vo_group_prefix . ':' . str_replace('+', '\+', urlencode($group_name)) . '(.*)';
    $entitlement_keycloak = $urn_namespace . ":group:" .  $vo_group_prefix . ':' . str_replace('+', '\+', urlencode($group_name));

    if ($urn_legacy) {
      $entitlement_regex = '(' . $entitlement_regex . ')|(^' . $urn_namespace . ':' . $urn_authority . ':(.*)@' . urlencode($group_name) . ')';
    }

    $users = $provisioner->retrieveUsersByEduPersonEntitlement($keycloak, $entitlement_keycloak);
    foreach ($users as $keycloak_user) {
      // Loop through the entitlements array and remove matching entitlements
      foreach ($keycloak_user->attributes->eduPersonEntitlement as $key => $entitlement) {
        if (preg_match('/' . $entitlement_regex . '/', $entitlement) && preg_match('/' . $regex . '/', $entitlement)) {
          unset($keycloak_user->attributes->eduPersonEntitlement[$key]);
        }
      }
      CakeLog::write('debug', __METHOD__ . ':: entitlements left: ' .  var_export($keycloak_user->attributes->eduPersonEntitlement, true), LOG_DEBUG);
      // TODO Remove comment
      //$provisioner->updatePersonEntitlements($keycloak, $keycloak_user);
    }
  }


  /**
   * renamentitlementsByGroup
   *
   * @param  mixed $keycloak
   * @param  mixed $old_group_name
   * @param  mixed $new_group_name
   * @param  mixed $urn_namespace
   * @param  mixed $urn_legacy
   * @param  mixed $urn_authority
   * @param  mixed $vo_group_prefix
   * @return void
   */
  public static function renameEntitlementsByGroup($provisioner, $keycloak, $old_group_name, $new_group_name,  $urn_namespace, $urn_legacy, $urn_authority, $vo_group_prefix)
  {
    if (strpos($keycloak->entitlementFormat, "/") == 0) {
      $regex = explode('/', $keycloak->entitlementFormat)[1];
    } else {
      $regex = $keycloak->entitlementFormat;
    }
    $entitlement_regex = '^' . $urn_namespace . ':group:' . $vo_group_prefix . ':' . str_replace('+', '\+', urlencode($old_group_name)) . '(.*)';
    $entitlement_keycloak = $urn_namespace . ":group:" .  $vo_group_prefix . ':' . str_replace('+', '\+', urlencode($old_group_name));

    if ($urn_legacy) {
      $entitlement_regex = '(' . $entitlement_regex . ')|(^' . $urn_namespace . ':' . $urn_authority . ':(.*)@' . str_replace('+', '\+', urlencode($old_group_name)) . ')';
    }

    // Loop through the entitlements array and update matching entitlements
    $users = $provisioner->retrieveUsersByEduPersonEntitlement($keycloak, $entitlement_keycloak);
    foreach ($users as $keycloak_user) {
      // Loop through the entitlements array and remove matching entitlements
      foreach ($keycloak_user->attributes->eduPersonEntitlement as $key => $entitlement) {
        if (preg_match('/' . $entitlement_regex . '/', $entitlement) && preg_match('/' . $regex . '/', $entitlement)) {
          $keycloak_user->attributes->eduPersonEntitlement[$key] = str_replace(urlencode($old_group_name), urlencode($new_group_name), $entitlement);
        }
      }
      CakeLog::write('debug', __METHOD__ . ':: entitlements after renaming: ' . var_export($keycloak_user->attributes->eduPersonEntitlement, true), LOG_DEBUG);
      // TODO Remove comment
      //$provisioner->updatePersonEntitlements($keycloak, $keycloak_user);
    }
  }

  /**
   * renameEntitlementsByCou
   *
   * @param  mixed $keycloak
   * @param  mixed $old_group_name
   * @param  mixed $new_group_name
   * @param  mixed $urn_namespace
   * @param  mixed $urn_legacy
   * @param  mixed $urn_authority
   * @param  mixed $vo_group_prefix
   * @return void
   */
  public static function renameEntitlementsByCou($provisioner, $keycloak, $old_cou_name, $new_cou_name,  $urn_namespace, $urn_legacy, $urn_authority)
  {
    if (strpos($keycloak->entitlementFormat, "/") == 0) {
      $regex = explode('/', $keycloak->entitlementFormat)[1];
    } else {
      $regex = $keycloak->entitlementFormat;
    }

    $group = !empty($group_name) ? ":" . $group_name : "";
    // old_cou_name and new_cou_name are already url_encoded
    $entitlement_regex = '^' . $urn_namespace . ":group:" . str_replace('+', '\+', $old_cou_name) . $group . ":(.*)#" . $urn_authority;
    $entitlement_keycloak = $urn_namespace . ":group:" .  str_replace('+', '\+', $old_cou_name) . $group . ":";

    if ($urn_legacy) {
      $entitlement_regex = '(' . $entitlement_regex . ')|(^' . $urn_namespace . ":group:" . str_replace('+', '\+', $old_cou_name) . '#' . $urn_authority . ')';
    }

    // Loop through the entitlements array and update matching entitlements
    $users = $provisioner->retrieveUsersByEduPersonEntitlement($keycloak, $entitlement_keycloak);
    foreach ($users as $keycloak_user) {
      // Loop through the entitlements array and remove matching entitlements
      foreach ($keycloak_user->attributes->eduPersonEntitlement as $key => $entitlement) {
        if (preg_match('/' . $entitlement_regex . '/', $entitlement) && preg_match('/' . $regex . '/', $entitlement)) {
          $keycloak_user->attributes->eduPersonEntitlement[$key] = str_replace($old_cou_name, $new_cou_name, $entitlement);
        }
      }
      CakeLog::write('debug', __METHOD__ . ':: entitlements after renaming: ' .  var_export($keycloak_user->attributes->eduPersonEntitlement, true), LOG_DEBUG);
      // TODO Remove comment
      //$provisioner->updatePersonEntitlements($keycloak, $keycloak_user);
    }
  }

  /**
   * deleteAllEntitlements
   *
   * @param  mixed $keycloak
   * @param  integer $user_id
   * @return void
   */
  public static function deleteAllEntitlements($keycloak, $person)
  {

    CakeLog::write('debug', __METHOD__ . ':: delete all entitlements from keycloak for user.', LOG_DEBUG);
    $person->attributes->eduPersonEntitlement = [];
    //TODO: Uncomment for OPENAIRE BETA
    //$this->updatePersonEntitlements($keycloak, $person);
  }

  /**
   * RCIAM Defines into the VO Whitelist ONLY the parent of a given COU hierarchy.
   * This method will fetch the children, if any, of the whitelisted VOs
   *
   * @param array $vo_white_list
   * @return [string]
   */
  public static function getVoWitelistChildren($vo_white_list)
  {
    // Get list of id => cou_name
    $args = array();
    $args['conditions']['Cou.name'] = $vo_white_list;
    $args['contain'] = false;
    $args['fields'] = array('id', 'name');

    $Cou = ClassRegistry::init('Cou');
    $cou_list = $Cou->find('list', $args);

    $cou_children = array();
    foreach ($cou_list as $id => $cou_name) {
      if ($Cou->childCount($id) > 0) {
        $children = $Cou->children($id);
        $children_names = Hash::extract($children, '{n}.Cou.name');
        $cou_children = array_merge($cou_children, $children_names);
      }
    }

    return $cou_children;
  }

  /**
   * insertNewEntitlements
   *
   * @param  mixed $keycloak
   * @param  integer $user_id
   * @param  array $current_entitlements
   * @param  array $new_entitlements
   * @return void
   */
  public static function insertNewEntitlements($keycloak, &$person, $new_entitlements)
  {
    $current_entitlements = $person->attributes->eduPersonEntitlement;
    $insertEntitlements = array_diff($new_entitlements, $current_entitlements);
    if (!empty($insertEntitlements)) {
      CakeLog::write('debug', __METHOD__ . ':: entitlements to be inserted to Keycloak' . print_r($insertEntitlements, true), LOG_DEBUG);
      foreach ($insertEntitlements as $new_entitlement) {
        array_push($person->attributes->eduPersonEntitlement, $new_entitlement);
      }

      CakeLog::write('debug', __METHOD__ . ':: entitlements keycloak ' . var_export($person->attributes->eduPersonEntitlement, true), LOG_DEBUG);
    }
  }
}
