<?php

class CRM_Chmosaicotemplate_TemplateWrapper implements API_Wrapper {

  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    $values = is_array($result) ? $result['values'] : $result->getArrayCopy(); // hand both api3 and api4

    foreach ($values as $index => $template) {
      if (isset($template['metadata'])) {
        $metadata = json_decode($template['metadata'], TRUE);
        if ($template['title'] == 'Basic - Email With Gallery') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Email%20With%20Gallery.jpg';
        }
        elseif ($template['title'] == 'Basic - Email No Gallery') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Email%20No%20Gallery.jpg';
        }
        elseif ($template['title'] == 'Basic - Newsletter') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Newsletter.jpg';
        }
        elseif ($template['title'] == 'Basic - Text Only') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Text%20Only.jpg';
        }
        elseif ($template['title'] == 'Basic - Thank You Email') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Thank%20You%20Email.jpg';
        }
        $values[$index]['metadata'] = json_encode($metadata);
      }
    }

    if ( is_array($result)  ) {
      $result['values'] = $values;
    } else {
      $result->exchangeArray($values);
    }
    return $result;
  }

}     
