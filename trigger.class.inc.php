<?php

/**
 * Module Alarm Clock attribute
 * https://github.com/itop-itsm-ru/alarm-clock-attribute
 *
 * @author      Vladimir Kunin <v.b.kunin@gmail.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

class TriggerOnAlarmClock extends TriggerOnObject
{
  public static function Init()
  {
    $aParams = array
    (
      "category" => "core/cmdb,bizmodel",
      "key_type" => "autoincrement",
      "name_attcode" => "description",
      "state_attcode" => "",
      "reconc_keys" => array('description'),
      "db_table" => "priv_trigger_onalarmclock",
      "db_key_field" => "id",
      "db_finalclass_field" => "",
      "display_template" => "",
    );
    MetaModel::Init_Params($aParams);
    MetaModel::Init_InheritAttributes();

    MetaModel::Init_AddAttribute(new AttributeString("alarm_clock_attcodes", array("allowed_values"=>null, "sql"=>"alarm_clock_attcodes", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));

    // Display lists
    MetaModel::Init_SetZListItems('details', array('description', 'target_class', 'filter', 'alarm_clock_attcodes', 'action_list')); // Attributes to be displayed for the complete details
    MetaModel::Init_SetZListItems('list', array('target_class', 'alarm_clock_attcodes')); // Attributes to be displayed for a list
    // Search criteria
    MetaModel::Init_SetZListItems('standard_search', array('description', 'target_class')); // Criteria of the std search form
    //MetaModel::Init_SetZListItems('advanced_search', array('name')); // Criteria of the advanced search form
  }
}
