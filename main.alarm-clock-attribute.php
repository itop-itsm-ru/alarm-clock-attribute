<?php

/**
 * Module Alarm Clock attribute
 * https://github.com/itop-itsm-ru/alarm-clock-attribute
 *
 * @author      Vladimir Kunin <v.b.kunin@gmail.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

class ormAlarmClock
{
  protected $iAlarmTime; // unix time (seconds)
  protected $bEnabled; //

  /**
   * Constructor
   */
  public function __construct($iAlarmTime = null, $bEnabled = false)
  {
    $this->iAlarmTime = $iAlarmTime;
    $this->bEnabled = $bEnabled;
  }

  /**
   * Necessary for the triggers
   */
  public function __toString()
  {
    return (string) $this->iAlarmTime;
  }

  public function GetAlarmUnixTime()
  {
    return $this->iAlarmTime;
  }

  public function Disable()
  {
    $this->bEnabled = false;
  }

  public function IsEnabled()
  {
    return $this->bEnabled;
  }

  // public function IsAlarmTimePassed($iAlarmTime)
  // {
  //   if (time() > $iAlarmTime) return true;
  //   return false;
  // }
}

class CheckEnabledAlarmClocks implements iBackgroundProcess
{
  public function GetPeriodicity()
  {
    return 60; // seconds
  }

  public function Process($iTimeLimit)
  {
    $aList = array();
    foreach (MetaModel::GetClasses() as $sClass)
    {
      foreach (MetaModel::ListAttributeDefs($sClass) as $sAttCode => $oAttDef)
      {
        if ($oAttDef instanceof AttributeAlarmClock)
        {
          $sNow = date('Y-m-d H:i:s');
          $sExpression = "SELECT {$sClass} WHERE {$sAttCode} < '{$sNow}' AND {$sAttCode}_ac_enabled = 1";
          $oFilter = DBObjectSearch::FromOQL($sExpression);
          $oSet = new DBObjectSet($oFilter);
          while ((time() < $iTimeLimit) && ($oObj = $oSet->Fetch()))
          {
            $sClass = get_class($oObj);
            $aList[] = $sClass.'::'.$oObj->GetKey().' '.$sAttCode;

            foreach ($oAttDef->ListActions() as $iAction => $aActionData)
            {
              $sVerb = $aActionData['verb'];
              $aParams = $aActionData['params'];
              $aValues = array();
              foreach($aParams as $def)
              {
                if (is_string($def))
                {
                  // Old method (pre-2.1.0) non typed parameters
                  $aValues[] = $def;
                }
                else // if(is_array($def))
                {
                  $sParamType = array_key_exists('type', $def) ? $def['type'] : 'string';
                  switch($sParamType)
                  {
                    case 'int':
                      $value = (int)$def['value'];
                      break;

                    case 'float':
                      $value = (float)$def['value'];
                      break;

                    case 'bool':
                      $value = (bool)$def['value'];
                      break;

                    case 'reference':
                      $value = ${$def['value']};
                      break;

                    case 'string':
                    default:
                      $value = (string)$def['value'];
                  }
                  $aValues[] = $value;
                }
              }
              $aCallSpec = array($oObj, $sVerb);
              call_user_func_array($aCallSpec, $aValues);
            }

            // Disable alarm clock
            $oAlarmClock = $oObj->Get($sAttCode);
            $oAlarmClock->Disable();
            $oObj->Set($sAttCode, $oAlarmClock);
            if($oObj->IsModified())
            {
              // TO-DO: What's about history? GetTrackingLevel()?
              CMDBObject::SetTrackInfo("Automatic - alarm clock triggered");
              $oMyChange = CMDBObject::GetCurrentChange();
              $oObj->DBUpdateTracked($oMyChange, true /*skip security*/);
            }

            // Find and activate triggers
            // TO-DO: many alarm clock attcodes without regexp
            $sClassList = implode("', '", MetaModel::EnumParentClasses($sClass, ENUM_PARENT_CLASSES_ALL));
            $oTriggerSet = new DBObjectSet(
              DBObjectSearch::FromOQL("SELECT TriggerOnAlarmClock AS t WHERE t.target_class IN ('$sClassList') AND :attcode REGEXP alarm_clock_attcodes"),
              array(), // order by
              array('attcode' => $sAttCode)
            );
            // Add placeholders $alarm->attcode$, $alarm->value$ for use in notifications
            $aContextArgs = $oObj->ToArgs('this');
            $aContextArgs['alarm->attcode'] = $oAttDef->GetLabel();
            $aContextArgs['alarm->value'] = $oAttDef->GetAsHtml($oAlarmClock);
            while ($oTrigger = $oTriggerSet->Fetch())
            {
              $oTrigger->DoActivate($aContextArgs);
            }
          }
        }
      }
    }

    $iProcessed = count($aList);
    return "Triggered $iProcessed alarm(s):".implode(", ", $aList);
  }
}