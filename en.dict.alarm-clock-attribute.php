<?php

/**
 * Module Alarm Clock attribute
 * https://github.com/itop-itsm-ru/alarm-clock-attribute
 *
 * @author      Vladimir Kunin <v.b.kunin@gmail.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

Dict::Add('EN US', 'English', 'English', array(
  'Class:TriggerOnAlarmClock' => 'Trigger on alarm clock',
  'Class:TriggerOnAlarmClock/Attribute:alarm_clock_attcodes' => 'Alarm clock',
  'Class:TriggerOnAlarmClock/Attribute:alarm_clock_attcodes+' => 'Specify the attribute code(s) of the alarm clock when triggered which should activate the trigger. You can specify multiple attributes by using the SQL REGEXP: ^move2production$|^attr_code$|^another_att_code$',
  'Class:TriggerOnAlarmClock/Attribute:alarm_clock_attcodes?' => 'Specify the attribute code(s) of the alarm clock when triggered which should activate the trigger. You can specify multiple attributes by using the SQL REGEXP: ^move2production$|^attr_code$|^another_att_code$',
));
