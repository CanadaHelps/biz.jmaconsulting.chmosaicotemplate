<?php
use CRM_Chmosaicotemplate_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Chmosaicotemplate_Upgrader extends CRM_Chmosaicotemplate_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
    $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
      'return' => array("id"),
      'name' => "customFieldCreatedViaManagedHook",
    ));
    civicrm_api3('Setting', 'create', array(
      'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Perform Cleanup routine
   */
  public function cleanupDatabaseTemplates() {
    civicrm_api3('Extension', 'disable', ['key' => 'org.civicrm.mosaicomsgtpl']);
    $messageTemplates = civicrm_api3('MessageTemplate', 'get', [
      'is_reserved' => 1,
      'workflow_id' => ['IS NULL' => 1],
      'msg_title' => ['NOT LIKE' => '%Thank You Email%'],
      'options' => ['limit' => 0],
    ]);
    if (!empty($messageTemplates['values'])) {
      foreach ($messageTemplates['values'] as $template) {
        try {
          civicrm_api3('MessageTemplate', 'delete', ['id' => $template['id']]);
        }
        catch (Exception $e) {
          \Civi::log()->debug('Unable to delete MessageTemplate ID', ['id' => $template['id']]);
        }
      }
    }
    $copyOfThankYou = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'Copy of Basic - Thank You Email',
      'is_reserved' => 1,
      'workflow_id' => ['IS NULL' => 1],
    ]);
    if (!empty($copyOfThankYou['values'])) {
      foreach ($copyOfThankYou['values'] as $template) {
        try {
          civicrm_api3('MessageTemplate', 'delete', ['id' => $template['id']]);
        }
        catch (Exception $e) {
          \Civi::log()->debug('Unable to delete MessageTemplate ID', ['id' => $template['id']]);
        }
      }
    }
    $thankYouTemplate = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'Basic - Thank You Email',
    ]);
    $msg_html = file_get_contents(__DIR__ . '/thank_you_email_fixed_content.html');
    civicrm_api3('MessageTemplate', 'create', [
      'id' => $thankYouTemplate['id'],
      'msg_html' => $msg_html,
      'is_reserved' => 0,
    ]);
    $mosaicoThankYou = civicrm_api3('MosaicoTemplate', 'get', ['title' => 'Basic - Thank You Email']);
    if (!empty($mosaicoThankYou['values'])) {
      civicrm_api3('MosaicoTemplate', 'delete', ['id' => $mosaicoThankYou['id']]);
    }
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1200() {
    $this->ctx->log->info('Applying update 1200');
    $this->cleanupDatabaseTemplates();
    return TRUE;
  }

  public function upgrade_1201() {
    $this->ctx->log->info('Applying update 1201: Fix tokens used in the basic thank you email template');
    $this->fixUpBasicThankYouTemplate();
    return TRUE;
  }

  public function upgrade_1202() {
    $this->ctx->log->info('Applying update 1202: Update to Joe\'s fixed version of the template');
    $this->fixUpBasicThankYouTemplate();
    return TRUE;
  }

  public function upgrade_1203() {
    $this->ctx->log->info('CRM-988: Relocate "Unsubscribe" Link to the Footer');
   //Replacing uk.co.vedaconsulting.mosaico template path with biz.jmaconsulting.chmosaicotemplate for canadahelps base(Basic) templates (4 templates)
    $whereClauses = [
      [
        'searchString' => 'common/uk.co.vedaconsulting.mosaico/packages/mosaico/templates/versafix-1/template-versafix-1.html',
        'searchClause' => "metadata LIKE '%common/uk.co.vedaconsulting.mosaico/packages/mosaico/templates/versafix-1/template-versafix-1.html%'",
        'replaceString' => 'common/biz.jmaconsulting.chmosaicotemplate/chtemplate/chtemplate.html',
      ],
      [
        'searchString' => 'vendor/civicrm/uk.co.vedaconsulting.mosaico/packages/mosaico/templates/versafix-1/template-versafix-1.html',
        'searchClause' => "metadata LIKE '%vendor/civicrm/uk.co.vedaconsulting.mosaico/packages/mosaico/templates/versafix-1/template-versafix-1.html%'",
        'replaceString' => 'vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/chtemplate.html',
      ]
    ];
    foreach($whereClauses as $whereClause) {
     $queryValue = CRM_Core_DAO::executeQuery(sprintf("SELECT metadata, id from `civicrm_mosaico_template` WHERE %s ", $whereClause['searchClause']));
     while($queryValue->fetch())
     {
       $metavalue_array = json_decode($queryValue->metadata,TRUE);
       $metavalue_array['template'] = str_replace($whereClause['searchString'], $whereClause['replaceString'], $metavalue_array['template']);
       $updated_metavalue = json_encode($metavalue_array);
       CRM_Core_DAO::executeQuery(sprintf("UPDATE civicrm_mosaico_template SET metadata = '%s' WHERE id = %s ", $updated_metavalue, $queryValue->id));
      }
    }
   return TRUE;
  }

  public function upgrade_1205() {
    $this->ctx->log->info('CRM-1069: DMS - Default “Basic Thank You” E-mail Templates not populating “Contact First or Last Name” for Organizations & Households');
    $thankYouTemplate = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'Basic - Thank You Email',
    ]);
    //Replacing (contact.first_name) (contact.last_name) token with (contact.display_name) for "Basic - Thank You Email" message template
    if (!empty($thankYouTemplate['values'])) {
      foreach ($thankYouTemplate['values'] as $templateContent) {
        $updatedMsgHtml = str_replace("{contact.first_name} {contact.last_name}", "{contact.display_name}", $templateContent['msg_html'], $replaceCount);
        if($replaceCount>0){
          civicrm_api3('MessageTemplate', 'create', [
            'msg_html' => $updatedMsgHtml,
            'id' => $templateContent['id'],
          ]);
        }
      }
    }
   return TRUE;
  }

  public function fixUpBasicThankYouTemplate() {
    $thankYouTemplate = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'Basic - Thank You Email',
    ]);
    $msg_html = file_get_contents(__DIR__ . '/thank_you_email_fixed_content.html');
    civicrm_api3('MessageTemplate', 'create', [
      'id' => $thankYouTemplate['id'],
      'msg_html' => $msg_html,
      'is_reserved' => 0,
    ]);
  }

  public function upgrade_1206() {
    $this->ctx->log->info('CRM-1069: DMS - Correct Mosaico Template entries with non-existent msg_tpl_id');
    CRM_Core_DAO::executeQuery(
      "UPDATE civicrm_mosaico_template
      SET msg_tpl_id = NULL
      WHERE msg_tpl_id NOT IN ( SELECT id FROM civicrm_msg_template )"
    );

   return TRUE;
  }
  public function upgrade_1221() {
    $this->ctx->log->info('CRM-1748: fix template paths');
    $template = CRM_Core_DAO::executeQuery("SELECT metadata, id from `civicrm_mosaico_template`");
    while ($template->fetch()) {
      $metadata = json_decode($template->metadata, TRUE);
      if (preg_match('/^vendor\/civicrm\/zz-canadahelps\/biz.jmaconsulting.chmosaicotemplate\/.+/', $metadata['template'])) {
        $metadata['template'] = str_replace(
          'vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/',
          '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/',
          $metadata['template']);
        $updated_metavalue = json_encode($metadata);
        CRM_Core_DAO::executeQuery(sprintf("UPDATE civicrm_mosaico_template SET metadata = '%s' WHERE id = %s ", $updated_metavalue, $template->id));
      }
    }
    return TRUE;
  }



  ### BELOW THIS POINT: use new format. ### 
  ### Example: upgrade_13001 => 1.3.x, upgrade function 001 ###

  public function upgrade_13001() {
    $this->ctx->log->info('Fix template paths');
    $template = CRM_Core_DAO::executeQuery("SELECT metadata, id from `civicrm_mosaico_template`");
    while ($template->fetch()) {
      $metadata = json_decode($template->metadata, TRUE);
      $metadata['template'] = str_replace('vendor/civicrm/canadahelps/', 'vendor/civicrm/zz-canadahelps/', $metadata['template']);
      $metadata['thumbnail'] = str_replace('vendor/civicrm/canadahelps/', 'vendor/civicrm/zz-canadahelps/', $metadata['thumbnail']);
      $updated_metavalue = json_encode($metadata);
      CRM_Core_DAO::executeQuery(sprintf("UPDATE civicrm_mosaico_template SET metadata = '%s' WHERE id = %s ", $updated_metavalue, $template->id));
    }
    return TRUE;
  }


}
