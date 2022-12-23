<?php

namespace Drupal\easy_material_generate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Utility\Random; 

use Drupal\Component\Uuid;


use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

use \Drupal\node\Entity\Node;

use Drupal\block_content\Entity\BlockContentType;


//~ use Drupal\Component\Uuid\UuidInterface;
//~ use Drupal\Component\Uuid\Php;
//~ use Drupal\Component\Uuid\Uuid;

/**
 * Returns responses for Easy Material Generate routes.
 */
class EasyMaterialGenerateController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $action = 'generate_users';
    
    if($action=='generate_collapsible'){
      $values = \Drupal::service('easy_material.generate')->get_item_values();
      
      $control_blocks = [
        'basic' => [
          'block_content_type' => [
            'custom_block_type' => 'easy_material_collapsible_block',
            'id' => 'easy_material_collapsible_block',
            'label' => 'Easy Material collapsible block',
            'description' => 'Provide a basic collapsible block', 
            'field_name' => 'field_collapsible',
            'field_label' => 'Collapsible items',
            'field_storage_type' => 'easy_material_collapsible_field',
            'view_display_type' => 'easy_material_collapsible_field_default',
            'form_display_type' => 'easy_material_collapsible_field',         
          ],
          'block_content' => [
            'type' => 'easy_material_collapsible_block',
            'info' => 'Collapsible block',
            'values' => $values['basic_values'],
            'field_name' => 'field_collapsible',
          ]
        ],
        
        'extended' => [
          'block_content_type' => [
            'custom_block_type' => 'em_extended_collapsible_block',
            'id' => 'em_extended_collapsible_block',
            'label' => 'Easy Material extended collapsible block',
            'description' => 'Provide an extended collapsible block',          
            'field_name' => 'field_extended_collapsible',
            'field_label' => 'Extended collapsible items',
            'field_storage_type' => 'easy_material_extended_collapsible_field',
            'view_display_type' => 'easy_material_extended_collapsible_field_default',
            'form_display_type' => 'easy_material_extended_collapsible_field',
          ],
          'block_content' => [
            'type' => 'em_extended_collapsible_block',
            'info' => 'Extended collapsible block',
            'values' => $values['extended_values'],
            'field_name' => 'field_extended_collapsible',
          ]
        ],
      ];
      
      
      
      foreach($control_blocks as $key=>$block_data){
        $block_exists = \Drupal::entityTypeManager()->getStorage('block_content_type')
          ->load($block_data['block_content_type']['custom_block_type'])?TRUE:FALSE;
        $result = FALSE;
        if(!$block_exists){
          $newBlockType = BlockContentType::create([
            'id' => $block_data['block_content_type']['id'],
            'label' => $block_data['block_content_type']['label'],
            'description' => $block_data['block_content_type']['description'],
          ]);
          $result = $newBlockType->save();
          
          $field = FieldConfig::loadByName('block_content', $block_data['block_content_type']['id'], $block_data['block_content_type']['field_name']);
          if (empty($field)){
            
            $fieldStorage = FieldStorageConfig::create([
              'field_name' => $block_data['block_content_type']['field_name'],
              'entity_type' => 'block_content',
              'type' => $block_data['block_content_type']['field_storage_type'],
            ]);
            $fieldStorage->setCardinality(-1);
            $fieldStorage->save();
            $field = FieldConfig::create([
              'field_storage' => $fieldStorage,
              'bundle' => $block_data['block_content_type']['id'],
              'label' => $block_data['block_content_type']['label'],
            ]);
            $field->save();
            
            $display_repository = \Drupal::service('entity_display.repository');
            $display_repository->getFormDisplay('block_content', $block_data['block_content_type']['id'])
              ->setComponent($block_data['block_content_type']['field_name'], [
                'type' => $block_data['block_content_type']['form_display_type'],
              ])
              ->save();
            
            $display_repository->getViewDisplay('block_content', $block_data['block_content_type']['id'])
              ->setComponent($block_data['block_content_type']['field_name'], [
                'label' => 'hidden',
                'type' => $block_data['block_content_type']['view_display_type'],
              ])
              ->save();
            

          }
          
          
        }else{
          $result = TRUE;
        }
        
        if($result){
          $result = FALSE;
          $blocks = \Drupal::entityTypeManager()->getStorage('block_content')
            ->loadByProperties(['type' => $block_data['block_content']['type']]);
          if(!$blocks){
            
            $block = \Drupal::entityTypeManager()
              ->getStorage('block_content')
              ->create(['type' => $block_data['block_content']['type']])
              ->setInfo($block_data['block_content']['info']);
            
            $block->set($block_data['block_content']['field_name'],$block_data['block_content']['values']);
            $result = $block->save();
          }else{
            $result = TRUE;
          }
        }
        
        if($result){
          $instance_block_exists = FALSE;
          $blocks = \Drupal::entityTypeManager()->getStorage('block_content')
            ->loadByProperties(['type' => $block_data['block_content']['type']]);
          if($blocks){
            $block_content_exists = TRUE;
            foreach($blocks as $custom_block){
              if($custom_block->getInstances()){
                $instance_block_exists = TRUE;
              }
            }
          }
          if(!$instance_block_exists){
            $instance_values = [
              'id' => str_replace ('-',' ',\Drupal::service('uuid')->generate()),
              'plugin' => 'block_content:'.$custom_block->uuid(),
              'region' => 'sidebar_second',
              'settings' => [
                'label' => $block_data['block_content']['info'],
              ],
              'theme' => 'easy_material',
              'visibility' => [],
              'weight' => -10,
            ];
            $block = \Drupal\block\Entity\Block::create($instance_values);
            $block->save();
          }
        }
      
      }
      
      
      
      $control_fields = [
        'basic' => [
            'field_name' => 'field_collapsible',
            'field_label' => 'Collapsible items',
            'field_storage_type' => 'easy_material_collapsible_field',
            'view_display_type' => 'easy_material_collapsible_field_default',
            'form_display_type' => 'easy_material_collapsible_field',         
            'values' => $values['basic_values'],
        ],
        
        'extended' => [
            'field_name' => 'field_extended_collapsible',
            'field_label' => 'Extended collapsible items',
            'field_storage_type' => 'easy_material_extended_collapsible_field',
            'view_display_type' => 'easy_material_extended_collapsible_field_default',
            'form_display_type' => 'easy_material_extended_collapsible_field',
            'values' => $values['extended_values'],
        ],
        
      ];
      
      $entity_type = 'node';
      $bundle_type = 'demo_collapsible';
      $bundle_name = 'Demo collapsible';
      $bundles = \Drupal\node\Entity\NodeType::loadMultiple();
      $bundle_descritption = 'Provide a custom type with two collapsible fields: basic and extended';
      
      if(!array_key_exists($bundle_type,$bundles)){
        $type = \Drupal\node\Entity\NodeType::create([
          'type' => $bundle_type,
          'name' => $bundle_name,
          'description' => $bundle_descritption,
        ]);
        $type->save();
      }
      
      
      foreach($control_fields as $key=>$field_data){
        $fieldStorage = FieldStorageConfig::loadByName('node', $field_data['field_name']);
        if(!$fieldStorage){
          $fieldStorage = \Drupal::service('easy_material.generate')->createField($field_data['field_name'],$entity_type,$field_data['field_storage_type'],-1,$bundle_type,$field_data['field_label'],$field_data['form_display_type'],$field_data['view_display_type']);
        }
      }
      
      
      $node = Node::create(['type' => $bundle_type]);
      $node->set('title', 'Collapsible demo');
      foreach($control_fields as $key=>$field_data){
        $node->set($field_data['field_name'],$field_data['values']);
      }
      $node->enforceIsNew();
      $node->save();
      
    }
    
    
    
    
    if($action == 'generate_users'){
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
    
    
    
    if($action == 'generate_collapsible_node'){
      
      
      
      
      
        
        
        
        
        
        
        
      

    }
    
    

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
