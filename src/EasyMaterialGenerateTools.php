<?php

namespace Drupal\easy_material_generate;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Component\Utility\Random; 
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

class EasyMaterialGenerateTools {
  use StringTranslationTrait;
  
  function __construct() {
     $this->styles = [];
  }
  
  public function get_user_names() {
    return ['John Stone','Ponnappa Priya','Mia Wong','Peter Stanbridge','Natalie Lee-Walsh','Ang Li','Nguta Ithya','Tamzyn French','Salome Simoes','Trevor Virtue','Tarryn Campbell-Gillies','Eugenia Anders','Andrew Kazantzis','Verona Blair','Jane Meldrum','Maureen M. Smith','Desiree Burch','Daly Harry','Hayman Andrews','Ruveni Ellawala'];
  }
  
  public function get_browse_context(){
    
    $opts = [
      'http' =>[
        'method' => 'GET',
        'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
      ],
    ];
    
    return stream_context_create($opts);
    
    return ["http"=>["header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"]];
  }
  
  public function get_item_values(){
    $items = [];
    $values = [];
    $icons = ['home','attachment-alt','balance','car','card'];
    $classes = ['teal-text','blue-text','brown-text','orange-text','green-text'];
    $active_classes = ['teal white-text','blue white-text','brown white-text','orange white-text','green white-text'];
    for($i=0;$i<5;$i++){
      $random = new Random();
      $values[] = [
        'title' => $random->sentences(5),
        'content_value' => $random->paragraphs(5),
        'content_format' => 'basic_html',
        'icon' => $icons[$i],
        'classes' => $classes[$i],
        'active_classes' => $active_classes[$i],
      ];
    }
    $items['extended_values'] = $values;
    foreach($values as $key=>$value){
      unset($values[$key]['icon']);
      unset($values[$key]['classes']);
      unset($values[$key]['active_classes']);
    }
    $items['basic_values'] = $values;
    return $items;
  }
  
  public function createField($field_name,$entity_type,$field_type,$cardinality,$bundle,$label,$form_display_type,$view_display_type){
    $fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $field_type,
      'cardinality' => $cardinality,
    ]);
    $fieldStorage->save();
    
    $fieldBase = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => $bundle,
      'label' => $label,
    ]);
    $fieldBase->save();
    
    
    $display_repository = \Drupal::service('entity_display.repository');

    $display_repository->getFormDisplay($entity_type, $bundle)
      ->setComponent($field_name, [
        'type' => $form_display_type,
      ])
      ->save();

    $display_repository->getViewDisplay($entity_type, $bundle)
      ->setComponent($field_name, [
        'label' => 'hidden',
        'type' => $view_display_type,
      ])
      ->save();
    
    
    
    
    return $fieldStorage;
  }
            

}
