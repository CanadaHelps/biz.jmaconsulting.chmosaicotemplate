<?php

class CRM_Chmosaicotemplate_TemplateWrapper implements API_Wrapper {

  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    foreach ($result['values'] as $key => $res) {
      if (isset($res['metadata'])) {
        $metadata = json_decode($res['metadata'], TRUE);
        if ($res['title'] == 'Basic - Email With Gallery') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Email%20With%20Gallery.jpg';
        }
        elseif ($res['title'] == 'Basic - Email No Gallery') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Email%20No%20Gallery.jpg';
        }
        elseif ($res['title'] == 'Basic - Newsletter') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Newsletter.jpg';
        }
        elseif ($res['title'] == 'Basic - Text Only') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Text%20Only.jpg';
        }
        elseif ($res['title'] == 'Basic - Thank You Email') {
          $metadata['thumbnail'] = '/vendor/civicrm/zz-canadahelps/biz.jmaconsulting.chmosaicotemplate/chtemplate/thumbnails/Basic%20-%20Thank%20You%20Email.jpg';
        }
      }
      $result['values'][$key]['metadata'] = json_encode($metadata);
    }
    return $result;
  }

}     
