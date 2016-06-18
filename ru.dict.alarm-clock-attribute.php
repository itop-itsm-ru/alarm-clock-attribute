<?php

/**
 * Module Alarm Clock attribute
 * https://github.com/itop-itsm-ru/alarm-clock-attribute
 *
 * @author      Vladimir Kunin <v.b.kunin@gmail.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

Dict::Add('RU RU', 'Russian', 'Russian', array(
  'Class:TriggerOnAlarmClock' => 'Триггер на будильник',
  'Class:TriggerOnAlarmClock/Attribute:alarm_clock_attcodes' => 'Будильник',
  'Class:TriggerOnAlarmClock/Attribute:alarm_clock_attcodes+' => 'Укажите код атрибута будильника, при срабатывании которого должен активироваться триггер. Можно указать несколько атрибутов через SQL REGEXP: ^move2production$|^attr_code$|^another_att_code$',
  'Class:TriggerOnAlarmClock/Attribute:alarm_clock_attcodes?' => 'Укажите код атрибута будильника, при срабатывании которого должен активироваться триггер. Можно указать несколько атрибутов через SQL REGEXP: ^move2production$|^attr_code$|^another_att_code$',
));
