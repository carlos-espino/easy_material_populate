<?php

namespace Drupal\easy_material_populate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;


/**
 * Returns responses for Easy Material Populate routes.
 */
class EasyMaterialPopulateController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $action = 'populate_users';
    
    if($action == 'populate_users'){
      $user_names = ["John Stone","Ponnappa Priya","Mia Wong","Peter Stanbridge","Natalie Lee-Walsh","Ang Li","Nguta Ithya","Tamzyn French","Salome Simoes","Trevor Virtue","Tarryn Campbell-Gillies","Eugenia Anders","Andrew Kazantzis","Verona Blair","Jane Meldrum","Maureen M. Smith","Desiree Burch","Daly Harry","Hayman Andrews","Ruveni Ellawala"];
      
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $context = stream_context_create(
          array(
              "http" => array(
                  "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
              )
          )
      );
      
      
      
      foreach($user_names as $user_name){
        $user = \Drupal\user\Entity\User::create();
        $user->setPassword('password');
        $user->enforceIsNew();
        $user->setEmail(str_replace(' ','.',$user_name).'@mail.com');
        $user->setUsername($user_name);
        
        $user->activate();
        
        
        $file_data = file_get_contents('https://i.pravatar.cc/300',false,$context);
        $picture_name = str_replace(' ','_',$user_name);
        $file = file_save_data($file_data,'public://'.$picture_name.'.jpg', FileSystemInterface::EXISTS_REPLACE);
        $user->set('user_picture', $file->id());
        $result = $user->save();
        
      }
      
    }

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
