<?php

/**
 * Module Alarm Clock attribute
 * https://github.com/itop-itsm-ru/alarm-clock-attribute
 *
 * @author      Vladimir Kunin <v.b.kunin@gmail.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

class AttributeAlarmClock extends AttributeDefinition
{
  // List of mandatory datamodel params (xml tags)
  static public function ListExpectedParams()
  {
    return array_merge(parent::ListExpectedParams(), array("actions"));
  }

  // How to display when editing
  public function GetEditClass() {return "DateTime";}

  public function IsDirectField() {return true;}
  public function IsScalar() {return true;}
  public function IsWritable() {return true;}

  /**
   * Returns an empty alarm clock as default value (date = null, enabled = false)
   * Uses when creating new object with alarm clock
   */
  public function GetDefaultValue() {
    return new ormAlarmClock();
  }

  public function GetEditValue($value, $oHostObj = null)
  {
    return self::SecondsToDate($value->GetAlarmUnixTime());
  }

  // Allows user to set value from string
  public function MakeRealValue($proposedValue, $oHostObj)
  {
    if (!$proposedValue instanceof ormAlarmClock)
    {
      // Turn off the alarm clock if value in null or empty string
      if ((is_null($proposedValue) || $proposedValue === '') && $this->IsNullAllowed()) {
        return new ormAlarmClock();
      }
      elseif (is_string($proposedValue)) {
        $iSeconds = self::DateToSeconds($proposedValue);
        return new ormAlarmClock($iSeconds, $iSeconds > time());
      }
    }
    return $proposedValue;
  }

  public static function DateToSeconds($sDate)
  {
    if (is_null($sDate))
    {
      return null;
    }
    $oDateTime = new DateTime($sDate);
    $iSeconds = $oDateTime->format('U');
    return $iSeconds;
  }

  public static function SecondsToDate($iSeconds)
  {
    if (is_null($iSeconds))
    {
      return null;
    }
    return date("Y-m-d H:i:s", $iSeconds);
  }

  // returns suffix/expression pairs (1 in most of the cases), for READING (Select)
  public function GetSQLExpressions($sPrefix = '')
  {
    if ($sPrefix == '')
    {
      $sPrefix = $this->GetCode();
    }
    $aColumns = array();

    $aColumns[''] = $sPrefix.'_ac_datetime';
    $aColumns['_ac_enabled'] = $sPrefix.'_ac_enabled';
    return $aColumns;
  }

  //returns a value out of suffix/value pairs, for SELECT result interpretation
  public function FromSQLToValue($aCols, $sPrefix = '')
  {
    $aExpectedCols = array($sPrefix, $sPrefix.'_ac_enabled');
    foreach ($aExpectedCols as $sExpectedCol)
    {
      if (!array_key_exists($sExpectedCol, $aCols))
      {
        $sAvailable = implode(', ', array_keys($aCols));
        throw new MissingColumnException("Missing column '$sExpectedCol' from {$sAvailable}");
      }
    }

    $value = new ormAlarmClock(
      self::DateToSeconds($aCols[$sPrefix]),
      (bool)($aCols[$sPrefix.'_ac_enabled'] == 1)
    );
    return $value;
  }

  // returns column/value pairs (1 in most of the cases), for WRITING (Insert, Update)
  public function GetSQLValues($value)
  {
    if ($value instanceOf ormAlarmClock)
    {
      $aValues = array();
      $aValues[$this->GetCode().'_ac_datetime'] = self::SecondsToDate($value->GetAlarmUnixTime());
      $aValues[$this->GetCode().'_ac_enabled'] = $value->IsEnabled() ? '1' : '0';
    }
    else
    {
      $aValues = array();
      $aValues[$this->GetCode().'_ac_datetime'] = '';
      $aValues[$this->GetCode().'_ac_enabled'] = '';
    }
    return $aValues;
  }

  // returns column/spec pairs (1 in most of the cases), for STRUCTURING (DB creation)
  public function GetSQLColumns($bFullSpec = false)
  {
    $aColumns = array();
    $aColumns[$this->GetCode().'_ac_datetime'] = 'DATETIME';
    $aColumns[$this->GetCode().'_ac_enabled'] = 'TINYINT(1)';
    return $aColumns;
  }

  public function GetFilterDefinitions()
  {
    $aRes = array(
      $this->GetCode() => new FilterFromAttribute($this),
      $this->GetCode().'_ac_enabled' => new FilterFromAttribute($this, '_ac_enabled'),
    );
    return $aRes;
  }

  public function GetValidationPattern()
  {
    return "^(([0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30))))( (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9])){0,1}){0,1}|0000-00-00 00:00:00|0000-00-00)$";
  }

  public function GetBasicFilterOperators()
  {
    return array(
      "="=>"equals",
      "!="=>"differs from",
      "<"=>"before",
      "<="=>"before",
      ">"=>"after (strictly)",
      ">="=>"after",
      "SameDay"=>"same day (strip time)",
      "SameMonth"=>"same year/month",
      "SameYear"=>"same year",
      "Today"=>"today",
      ">|"=>"after today + N days",
      "<|"=>"before today + N days",
      "=|"=>"equals today + N days",
    );
  }

  public function GetBasicFilterLooseOperator()
  {
    return '=';
  }

  public function GetBasicFilterSQLExpr($sOpCode, $value)
  {
    $sQValue = CMDBSource::Quote(self::SecondsToDate($value->GetAlarmUnixTime()));

    switch ($sOpCode)
    {
    case '=':
    case '!=':
    case '<':
    case '<=':
    case '>':
    case '>=':
      return $this->GetSQLExpr()." $sOpCode $sQValue";
    case 'SameDay':
      return "DATE(".$this->GetSQLExpr().") = DATE($sQValue)";
    case 'SameMonth':
      return "DATE_FORMAT(".$this->GetSQLExpr().", '%Y-%m') = DATE_FORMAT($sQValue, '%Y-%m')";
    case 'SameYear':
      return "MONTH(".$this->GetSQLExpr().") = MONTH($sQValue)";
    case 'Today':
      return "DATE(".$this->GetSQLExpr().") = CURRENT_DATE()";
    case '>|':
      return "DATE(".$this->GetSQLExpr().") > DATE_ADD(CURRENT_DATE(), INTERVAL $sQValue DAY)";
    case '<|':
      return "DATE(".$this->GetSQLExpr().") < DATE_ADD(CURRENT_DATE(), INTERVAL $sQValue DAY)";
    case '=|':
      return "DATE(".$this->GetSQLExpr().") = DATE_ADD(CURRENT_DATE(), INTERVAL $sQValue DAY)";
    default:
      return $this->GetSQLExpr()." = $sQValue";
    }
  }

  public function GetAsHTML($value, $oHostObject = null, $bLocalize = true)
  {
    if (is_object($value))
    {
      return Str::pure2html(self::SecondsToDate($value->GetAlarmUnixTime()));
    }
    elseif ($value) {
      return Str::pure2html(date("Y-m-d H:i:s", (int) $value));
    }
  }

  public function GetAsCSV($value, $sSeparator = ',', $sTextQualifier = '"', $oHostObject = null, $bLocalize = true)
  {
    return $value->GetAlarmUnixTime();
  }

  public function GetAsXML($value, $oHostObject = null, $bLocalize = true)
  {
    return $value->GetAlarmUnixTime();
  }

  static protected function GetDateFormat($bFull = false)
  {
    if ($bFull)
    {
      return "Y-m-d H:i:s";
    }
    else
    {
      return "Y-m-d H:i";
    }
  }

  protected function GetBooleanLabel($bValue)
  {
    $sDictKey = $bValue ? 'yes' : 'no';
    return Dict::S('BooleanLabel:'.$sDictKey, 'def:'.$sDictKey);
  }

  /**
   * To expose internal values: Declare an attribute AttributeSubItem
   * and implement the GetSubItemXXXX verbs
   */
  public function GetSubItemSQLExpression($sItemCode)
  {
    $sPrefix = $this->GetCode();
    switch($sItemCode)
    {
    case 'ac_enabled':
      return array('' => $sPrefix.'_ac_enabled');
    }

    throw new CoreException("Unknown item code '$sItemCode' for attribute ".$this->GetHostClass().'::'.$this->GetCode());
  }

  public function GetSubItemValue($sItemCode, $value, $oHostObject = null)
  {
    $oAlarmClock = $value;
    switch($sItemCode)
    {
    case 'ac_enabled':
      return $oAlarmClock->IsEnabled();
    }

    throw new CoreException("Unknown item code '$sItemCode' for attribute ".$this->GetHostClass().'::'.$this->GetCode());
  }

  public function GetSubItemAsHTMLForHistory($sItemCode, $sValue)
  {
    switch($sItemCode)
    {
    case 'ac_enabled':
      return $sHtml = $this->GetBooleanLabel((int)$sValue);
    }
  }

  public function GetSubItemAsHTML($sItemCode, $value)
  {
    $sHtml = $value;

    switch($sItemCode)
    {
    case 'ac_enabled':
      $sHtml = $this->GetBooleanLabel((int)$value);
      break;
    }
    return $sHtml;
  }

  public function GetSubItemAsCSV($sItemCode, $value, $sSeparator = ',', $sTextQualifier = '"')
  {
    $sFrom = array("\r\n", $sTextQualifier);
    $sTo = array("\n", $sTextQualifier.$sTextQualifier);
    $sEscaped = str_replace($sFrom, $sTo, (string)$value);
    $sRet = $sTextQualifier.$sEscaped.$sTextQualifier;

    switch($sItemCode)
    {
    case 'ac_enabled':
      $sRet = $sTextQualifier.$this->GetBooleanLabel($value).$sTextQualifier;
      break;
    }
    return $sRet;
  }

  public function GetSubItemAsXML($sItemCode, $value)
  {
    $sRet = Str::pure2xml((string)$value);

    switch($sItemCode)
    {
    case 'ac_enabled':
      $sRet = $this->GetBooleanLabel($value);
      break;
    }
    return $sRet;
  }

  /**
   * Implemented for the HTML spreadsheet format!
   */
  public function GetSubItemAsEditValue($sItemCode, $value)
  {
    $sRet = $value;

    switch($sItemCode)
    {
    case 'ac_enabled':
      $sRet = $this->GetBooleanLabel($value);
      break;
    }
    return $sRet;
  }

  public function ListActions()
  {
    return $this->Get('actions');
  }

  // TO-DO: test comparison of two alarm clocks
  public function Equals($val1, $val2)
  {
    if ($val1 === $val2) return true;

    if (is_object($val1) != is_object($val2))
    {
      return false;
    }
    if (!is_object($val1))
    {
      // string ?
      // todo = implement this case ?
      return false;
    }

    // Both values are Objects
    if ($val1 == $val2)
    {
      return true;
    }
    return false;
  }
}

?>