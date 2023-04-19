<!--
/**
 * COmanage Registry CO Keycloak Provisioning Target Fields
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
 * @since         COmanage Registry 3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<?php
// Determine if fields are editable
$e = false;

if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
  $e = true;

// We shouldn't get here if we don't have at least read permission, but check just in case

if(!$e && !$permissions['view'])
  return false;

// Add breadcrumbs
print $this->element("coCrumb");
// Provisioning Targes Crumb
$args = array();
$args['plugin'] = null;
$args['controller'] = 'co_provisioning_targets';
$args['action'] = 'index';
$args['co'] = $cur_co['Co']['id'];
$this->Html->addCrumb(_txt('ct.co_provisioning_targets.pl'), $args);

// Current Crumb
$crumbTxt = _txt('op.' . $this->action . '-a', array(_txt('ct.co_keycloak_provisioner_targets.1')));
$this->Html->addCrumb($crumbTxt);

print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
print $this->Form->hidden('co_provisioning_target_id', array('default' => $vv_ptid)) . "\n";

// Variables i want accross the view
$token_key = $this->request->params['_Token']['key'];

?>
<script type ="text/javascript">
  // If enable_vo_whitelist is enabled, show the relevant options
  function fields_update_gadgets() {
    if(document.getElementById('CoKeycloakProvisionerTargetEnableVoWhitelist').checked) {
      $("#CoKeycloakProvisionerTargetVoWhitelist").closest("li").show('fade');
    } else {
      $("#CoKeycloakProvisionerTargetVoWhitelist").closest("li").hide('fade');
    }
  }
  function js_local_onload() {
    fields_update_gadgets();
  }
</script>

<div id="<?php print $this->action; ?>_keycloak" class="explorerContainer">
  <div id="keycloakProvisioner" class="personExplorer">
    <ul>
    
          <li id="fields-database" class="fieldGroup">
            <a href="#tabs-database" class="fieldGroupName">
              <em class="material-icons">indeterminate_check_box</em>
              <?php
                print _txt('pl.keycloak_provisioner.api.credentials');
              ?>
            </a>
            <div id="names-container" class="fields">
                <ul id="tabs-database" class="fields form-list">
                <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('api_base_url',_txt('fd.keycloak_provisioner.api_base_url')); ?>
                  <span class="required">*</span>
                </div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['api_base_url']) ? ''
                  : filter_var($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['api_base_url'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('api_base_url', array('size' => 250,'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('api_client_id',_txt('fd.keycloak_provisioner.api_client_id')); ?>
                  <span class="required">*</span>
                </div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['api_client_id']) ? ''
                  : filter_var($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['api_client_id'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->text('api_client_id', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('api_client_secret',_txt('fd.keycloak_provisioner.api_client_secret')); ?>
                  <span class="required">*</span>
                </div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($vv_api_client_secret) ? ''
                  : filter_var($vv_api_client_secret,FILTER_UNSAFE_RAW);
                print $this->Form->password('api_client_secret', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
          </ul>
        </div>
      </li>

      <li id="fields-stats" class="fieldGroup">
        <a href="#tabs-stats" class="fieldGroupName">
          <em class="material-icons">indeterminate_check_box</em>
          <?php print _txt('pl.keycloak_provisioner.pl_config'); ?>
        </a>
        <div id="names-container" class="fields">
          <ul id="tabs-stats" class="fields form-list">
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.keycloak_provisioner.enable.vowhitelist'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.enable.vowhitelist.desc'); ?></div>
              </div>
              <div class="field-info checkbox">
                <?php print ($e
                  ? $this->Form->input('enable_vo_whitelist', array('onChange' => 'fields_update_gadgets();')) . ' ' .
                  $this->Form->label('enable_vo_whitelist', _txt('pl.keycloak_provisioner.enable.vowhitelist'))
                  : filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['enable_vo_whitelist'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
              </div>
              <ul class="field-children">
                <li id="fields-stats" class="field-stack">
                  <div class="field-name">
                    <div class="field-title">
                      <?php print $this->Form->label('vo_whitelist',_txt('pl.keycloak_provisioner.vo_whitelist')); ?>
                    </div>
                    <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.vo_whitelist.desc'); ?></div>
                  </div>
                  <div class="field-info">
                    <?php
                    $intro = empty($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['vo_whitelist']) ? ""
                      : filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['vo_whitelist'],FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->textarea('vo_whitelist', array('size' => 4000, 'rows' => 5, 'value' => $intro));
                    ?>
                  </div>
                </li>
              </ul>
            </li>

            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('vo_roles',_txt('pl.keycloak_provisioner.vo_roles')); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.vo_roles.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $vo_roles_value = empty($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['vo_roles']) ? ""
                  : filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['vo_roles'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('vo_roles', array('size' => 256, 'value' => $vo_roles_value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('merge_entitlements',_txt('pl.keycloak_provisioner.merge_entitlements')); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.merge_entitlements.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
               $attrs = array();
                $attrs['value'] = (isset($co_keycloak_provisioner_targets) ? $co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['merge_entitlements'] : false);
                $attrs['empty'] = false;
        
                if($e) {
                  print $this->Form->checkbox('CoKeycloakProvisionerTarget.merge_entitlements',
                    $vv_persistent_list,
                    $attrs);

                  if($this->Form->isFieldError('merge_entitlements')) {
                    print $this->Form->error('merge_entitlements');
                  }
                } else {
                  print _txt('en.server.sql', null, $co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['merge_entitlements']);
                } 

                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('urn_namespace',_txt('pl.keycloak_provisioner.urn_namespace')); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.urn_namespace.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['urn_namespace']) ? ''
                  : filter_var($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['urn_namespace'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('urn_namespace', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('urn_authority',_txt('pl.keycloak_provisioner.urn_authority')); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.urn_authority.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['urn_authority']) ? ''
                  : filter_var($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['urn_authority'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('urn_authority', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('urn_legacy',_txt('pl.keycloak_provisioner.urn_legacy')); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.urn_legacy.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $attrs = array();
                $attrs['value'] = (isset($co_keycloak_provisioner_targets) ? $co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['urn_legacy'] : '');
                $attrs['empty'] = false;

                if($e) {
                  print $this->Form->checkbox('urn_legacy',
                    $vv_persistent_list,
                    $attrs);

                  if($this->Form->isFieldError('urn_legacy')) {
                    print $this->Form->error('urn_legacy');
                  }
                } else {
                  print _txt('en.server.sql', null, $co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['urn_legacy']);
                }

                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('vo_group_prefix',_txt('pl.keycloak_provisioner.vo_group_prefix')); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.vo_group_prefix.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['vo_group_prefix']) ? ''
                  : filter_var($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['vo_group_prefix'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('vo_group_prefix', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
            <li class="field-stack">
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('entitlement_format',_txt('pl.keycloak_provisioner.entitlement_format')); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.keycloak_provisioner.entitlement_format.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['entitlement_format']) ? ''
                  : filter_var($co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['entitlement_format'],FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->textarea('entitlement_format', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.keycloak_provisioner.enable.formatvowht.short'); ?>
                </div>
              </div>
              <div class="field-info checkbox">
                <?php
                if($e) {
                  $checked = !is_null(filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['entitlement_format_include_vowht'],FILTER_SANITIZE_SPECIAL_CHARS))
                             ? filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['entitlement_format_include_vowht'], FILTER_VALIDATE_BOOLEAN)
                             : false;
                  print $this->Form->checkbox('entitlement_format_include_vowht', array('checked' => $checked))
                    . ' '
                    . $this->Form->label('entitlement_format_include_vowht', _txt('pl.keycloak_provisioner.enable.formatvowht'));
                }
                else {
                  print filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['entitlement_format_include_vowht'],FILTER_SANITIZE_SPECIAL_CHARS);
                }
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.keycloak_provisioner.enable.rciam_external.short'); ?>
                </div>
              </div>
              <div class="field-info checkbox">
                <?php
                if($e) {
                  $checked = !is_null(filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['rciam_external_entitlements'],FILTER_SANITIZE_SPECIAL_CHARS))
                             ? filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['rciam_external_entitlements'], FILTER_VALIDATE_BOOLEAN)
                             : false;
                  print $this->Form->checkbox('rciam_external_entitlements', array('checked' => $checked))
                    . ' '
                    . $this->Form->label('rciam_external_entitlements', _txt('pl.keycloak_provisioner.enable.rciam_external'));
                }
                else {
                  print filter_var($co_keycloak_provisioner_targets[0]['CoKeycloakProvisionerTarget']['rciam_external_entitlements'],FILTER_SANITIZE_SPECIAL_CHARS);
                }
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php print ($e ? $this->Form->label('identifier_type', _txt('pl.keycloak_provisioner.identifier_type')) : _txt('pl.keycloak_provisioner.identifier_type')); ?><span class="required">*</span>
                </div>
              </div>
              <div class="field-info">
                <?php

                $attrs = array();
                $attrs['value'] = (isset($co_keycloak_provisioner_targets) ? $co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['identifier_type'] : '');
                $attrs['empty'] = false;

                if($e) {
                  print $this->Form->select('identifier_type',
                    $vv_identifier_type_list,
                    $attrs);

                  if($this->Form->isFieldError('type')) {
                    print $this->Form->error('type');
                  }
                } else {
                  print _txt('pl.keycloak_provisioner.identifier_type', null, $co_keycloak_provisioner_targets[0]["CoKeycloakProvisionerTarget"]['identifier_type']);
                }
                ?>
              </div>
            </li>
          </ul>
        </div>
      </li>
      <li id="fields-btn" class="fieldGroup">  <!-- Save Button -->
        <div id="names-container" class="fields">
          <ul class="fields form-list">
            <?php if($e): ?>
              <li class="fields-submit">
                <div class="field-name">
                  <span class="required"><?php print _txt('fd.req'); ?></span>
                </div>
                <div class="field-info">
                  <?php print $this->Form->submit($submit_label); ?>
                </div>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </li>
    </ul>
  </div> <!-- personExplorer -->
  <!-- This is currently not supported due to a bug in COmanage. --><?php //print $this->element("changelog"); ?>
</div> <!-- explorerContainer -->
<script type="text/javascript">
  function responseConstruct(msg, status, testBtn) {
    color = (status === 'error') ? 'red' : 'green';
    text = "<span style='color:" + color + "'>" + msg + "</span>";
    $("#connection-test-lbl").html(text);

    testBtn.toggleClass('ui-state-disabled');
    testBtn.find('span').remove();
    testBtn.text("<?php print _txt('fd.server.test_connection'); ?>");
    testBtn.removeAttr('disabled');
  }

  <!-- /* JS specific to these fields */ -->
  $(function() {
    // Explorer menu toggles
    $(".fieldGroupName").click(function(event) {
      event.preventDefault();
      $(this).next(".fields").slideToggle("fast");
      // toggle the +/- icon:
      if ($(this).find(".material-icons").text() == "indeterminate_check_box") {
        $(this).find(".material-icons").text("add_box");
      } else {
        $(this).find(".material-icons").text("indeterminate_check_box");
      }
    });

    // Update the default value each time i change
    $('input').on('keyup', function() {
      this.defaultValue = this.value;
    });
    $('select').on('change', function() {
      $("option[value=" + this.value + "]", this)
        .attr("selected", true).siblings()
        .removeAttr("selected");
    });

  });
</script>