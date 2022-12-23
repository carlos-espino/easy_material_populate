<?php
namespace Drupal\easy_material_generate\Commands;

use Drush\Commands\DrushCommands;




use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Utility\Random; 

use Drupal\Component\Uuid;


use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

use \Drupal\node\Entity\Node;

use Drupal\block_content\Entity\BlockContentType;




/**
 * Drush command file.
 */
class CustomCommands extends DrushCommands {
  /**
   * A custom Drush command to displays the given text.
   * 
   * @command easy-material-generate:collapsible
   * @param $text Argument with text to be printed
   * @option uppercase Uppercase the text
   * @aliases emg-collapsible
   */
  public function printMe($text = 'Hello world!', $options = ['uppercase' => FALSE]) {
    if ($options['uppercase']) {
      $text = strtoupper($text);
    }
    $this->output()->writeln($text);
  }
  
  
  /**
   * A custom Drush command to displays the given text.
   * 
   * @command easy-material-generate:users
   * @aliases emg-users
   */
  public function generate_users() {
    $user_names = \Drupal::service('easy_material.generate')->get_user_names();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $context = \Drupal::service('easy_material.generate')->get_browse_context();
    
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
  
  
}
