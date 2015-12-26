<?
class BatterieMonitor extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("HintergrundFarbcode", "000000");
        $this->RegisterPropertyString("TextFarbcode", "FFFFFF");
        $this->RegisterPropertyString("TextOKFarbcode", "00FF00");
        $this->RegisterPropertyString("TextLOWFarbcode", "FF0000");
        $this->RegisterPropertyString("TextSize", "14");
        $this->RegisterPropertyString("TextAusrichtungDD", "mitte");
        $this->RegisterPropertyInteger("Intervall", 21600);
        $this->RegisterPropertyInteger("WebFrontInstanceID", 0);
        $this->RegisterPropertyInteger("SmtpInstanceID", 0);
        $this->RegisterPropertyInteger("EigenesSkriptID", 0);
        $this->RegisterPropertyBoolean("PushMsgAktiv", false);
        $this->RegisterPropertyBoolean("EMailMsgAktiv", false);
        $this->RegisterPropertyBoolean("EigenesSkriptAktiv", false);
        $this->RegisterPropertyBoolean("BatterieBenachrichtigungCBOX", false);
        $this->RegisterPropertyString("BatterieBenachrichtigungTEXT", "Der Aktor -�AKTORNAME- mit der ID -�AKTORID- hat eine leere Batterie gemeldet!");
        $this->RegisterTimer("BMON_UpdateTimer", 0, 'BMON_Update($_IPS[\'TARGET\']);');
    }

    public function Destroy()
    {
        $this->UnregisterTimer("BMON_UpdateTimer");
        
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        
        //Variablenprofil erstellen
        $this->RegisterProfileBooleanEx("BMON.NeinJa", "Battery", "", "", Array(
                                             Array(false, "Nein",  "Battery", 0x00FF00),
                                             Array(true, "Ja",  "Warning", 0xFF0000)
        ));
        
        //Variablen anlegen und einstellen
        $this->RegisterVariableInteger("BatteryAktorsAnzahlVAR", "Batterie Aktoren - Gesamt");
        $this->RegisterVariableInteger("BatteryLowAnzahlVAR", "Batterie Aktoren - Leer");
        $this->RegisterVariableBoolean("BatteryLowExistVAR", "Batterie Aktoren - Leere vorhanden", "BMON.NeinJa");
		    $this->RegisterVariableString("TabelleBatteryAlleVAR", "Tabelle - Batterie Aktoren ALLE", "~HTMLBox");
		    $this->RegisterVariableString("TabelleBatteryLowVAR", "Tabelle - Batterie Aktoren LEER", "~HTMLBox");
		    IPS_SetIcon($this->GetIDForIdent("BatteryAktorsAnzahlVAR"), "Battery");
		    IPS_SetIcon($this->GetIDForIdent("BatteryLowAnzahlVAR"), "Battery");
		    IPS_SetIcon($this->GetIDForIdent("BatteryLowExistVAR"), "Battery");
		    IPS_SetIcon($this->GetIDForIdent("TabelleBatteryAlleVAR"), "Battery");
		    IPS_SetIcon($this->GetIDForIdent("TabelleBatteryLowVAR"), "Battery");
		        
		    //Timer erstellen
        $this->SetTimerInterval("BMON_UpdateTimer", $this->ReadPropertyInteger("Intervall"));
        		
     		//Update
     		$this->Update();
    }

    public function Update()
    {
				$Batterien_AR = $this->ReadBatteryStates();
				$BATcountAlle = @count($Batterien_AR["Alle"]);
				$BATcountLeer = @count($Batterien_AR["Leer"]);
				$this->SetValueInteger("BatteryAktorsAnzahlVAR", $BATcountAlle);
				$this->SetValueInteger("BatteryLowAnzahlVAR", $BATcountLeer);
				if ($BATcountLeer == 0)
				{
						$this->SetValueBoolean("BatteryLowExistVAR", false);
				}
				else
				{
						$this->SetValueBoolean("BatteryLowExistVAR", true);
				}
				$this->HTMLausgabeGenerieren($Batterien_AR, "Alle");
				$this->HTMLausgabeGenerieren($Batterien_AR, "Leer");
				return true;
    }
    
    public function Alle_Auslesen()
    {
    		$Batterien_AR = $this->ReadBatteryStates();
    		$BATcountAlle = @count($Batterien_AR["Alle"]);
				$BATcountLeer = @count($Batterien_AR["Leer"]);
				$this->SetValueInteger("BatteryAktorsAnzahlVAR", $BATcountAlle);
				$this->SetValueInteger("BatteryLowAnzahlVAR", $BATcountLeer);
				if ($BATcountLeer == 0)
				{
						$this->SetValueBoolean("BatteryLowExistVAR", false);
				}
				else
				{
						$this->SetValueBoolean("BatteryLowExistVAR", true);
				}
				
    		$this->HTMLausgabeGenerieren($Batterien_AR, "Alle");
    		if (isset($Batterien_AR["Alle"]))
    		{
    				return $Batterien_AR["Alle"];
    		}
    		else
    		{
    				return false;
    		}
    }
    
    public function Leere_Auslesen()
    {
    		$Batterien_AR = $this->ReadBatteryStates();
    		$BATcountAlle = @count($Batterien_AR["Alle"]);
				$BATcountLeer = @count($Batterien_AR["Leer"]);
				$this->SetValueInteger("BatteryAktorsAnzahlVAR", $BATcountAlle);
				$this->SetValueInteger("BatteryLowAnzahlVAR", $BATcountLeer);
				if ($BATcountLeer == 0)
				{
						$this->SetValueBoolean("BatteryLowExistVAR", false);
				}
				else
				{
						$this->SetValueBoolean("BatteryLowExistVAR", true);
				}
				
    		$this->HTMLausgabeGenerieren($Batterien_AR, "Leer");
    		if (isset($Batterien_AR["Leer"]))
    		{
    				return $Batterien_AR["Leer"];
    		}
    		else
    		{
    				return false;
    		}
    }
    
    private function ReadBatteryStates()
    {
				$InstanzIDsListAll[] = IPS_GetInstanceListByModuleID("{2FD7576A-D2AD-47EE-9779-A502F23CABB3}");  // FS20 HMS
    		$InstanzIDsListAll[] = IPS_GetInstanceListByModuleID("{EE4A81C6-5C90-4DB7-AD2F-F6BBD521412E}");  // HomeMatic
    		$InstanzIDsListAll[] = IPS_GetInstanceListByModuleID("{101352E1-88C7-4F16-998B-E20D50779AF6}");  // Z-Wave
    		$a = 0;
				$l = 0;
    		foreach ($InstanzIDsListAll as $InstanzIDsList)
    		{
						foreach ($InstanzIDsList as $InstanzID)
						{
						    //FS20 HMS
						    $VarID = @IPS_GetObjectIDByIdent('LowBatteryVar', $InstanzID);
								if ($VarID !== false)
								{
										$Var = IPS_GetVariable($VarID);
										$VarLastUpdated = $Var["VariableUpdated"];
										$VarLastUpdatedDiffSek = time() - $VarLastUpdated;
										$DeviceID = IPS_GetProperty($InstanzID, "DeviceID");
										$InstanzHersteller = IPS_GetInstance($InstanzID);
										$InstanzHersteller = $InstanzHersteller["ModuleInfo"]["ModuleName"];
										$LowBat = GetValueBoolean($VarID);
										if ($LowBat === true)
										{
									   		$Batterien_AR["Alle"][$a]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Alle"][$a]["Batterie"] = "LEER";
									   		$Batterien_AR["Alle"][$a]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Alle"][$a]["ID"] = $DeviceID;
									   		$Batterien_AR["Alle"][$a]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$Batterien_AR["Leer"][$l]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Leer"][$l]["Batterie"] = "LEER";
									   		$Batterien_AR["Leer"][$l]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Leer"][$l]["ID"] = $DeviceID;
									   		$Batterien_AR["Leer"][$l]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Leer"][$l]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Leer"][$l]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$a++;
									   		$l++;
										}
										else
										{
									   		$Batterien_AR["Alle"][$a]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Alle"][$a]["Batterie"] = "OK";
									   		$Batterien_AR["Alle"][$a]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Alle"][$a]["ID"] = $DeviceID;
									   		$Batterien_AR["Alle"][$a]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$a++;
										}
						  	}
						    
						    //HomeMatic
						    $VarID = @IPS_GetObjectIDByIdent('LOWBAT', $InstanzID);
								if ($VarID !== false)
								{
										$Var = IPS_GetVariable($VarID);
										$VarLastUpdated = $Var["VariableUpdated"];
										$VarLastUpdatedDiffSek = time() - $VarLastUpdated;
										$DeviceID = IPS_GetProperty($InstanzID, "Address");
										$InstanzHersteller = IPS_GetInstance($InstanzID);
										$InstanzHersteller = $InstanzHersteller["ModuleInfo"]["ModuleName"];
										$LowBat = GetValueBoolean($VarID);
										if ($LowBat === true)
										{
									   		$Batterien_AR["Alle"][$a]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Alle"][$a]["Batterie"] = "LEER";
									   		$Batterien_AR["Alle"][$a]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Alle"][$a]["ID"] = $DeviceID;
									   		$Batterien_AR["Alle"][$a]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$Batterien_AR["Leer"][$l]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Leer"][$l]["Batterie"] = "LEER";
									   		$Batterien_AR["Leer"][$l]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Leer"][$l]["ID"] = $DeviceID;
									   		$Batterien_AR["Leer"][$l]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Leer"][$l]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Leer"][$l]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$a++;
									   		$l++;
										}
										else
										{
									   		$Batterien_AR["Alle"][$a]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Alle"][$a]["Batterie"] = "OK";
									   		$Batterien_AR["Alle"][$a]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Alle"][$a]["ID"] = $DeviceID;
									   		$Batterien_AR["Alle"][$a]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$a++;
										}
						  	}
						  	
						  	//Z-Wave
						  	$VarID = @IPS_GetObjectIDByIdent('BatteryLowVariable', $InstanzID);
								if ($VarID !== false)
								{
										$Var = IPS_GetVariable($VarID);
										$VarLastUpdated = $Var["VariableUpdated"];
										$VarLastUpdatedDiffSek = time() - $VarLastUpdated;
										$DeviceID = IPS_GetProperty($InstanzID, "NodeID");
										$InstanzHersteller = IPS_GetInstance($InstanzID);
										$InstanzHersteller = $InstanzHersteller["ModuleInfo"]["ModuleName"];
										$LowBat = GetValueBoolean($VarID);
										if ($LowBat === true)
										{
									   		$Batterien_AR["Alle"][$a]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Alle"][$a]["Batterie"] = "LEER";
									   		$Batterien_AR["Alle"][$a]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Alle"][$a]["ID"] = $DeviceID;
									   		$Batterien_AR["Alle"][$a]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$Batterien_AR["Leer"][$l]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Leer"][$l]["Batterie"] = "LEER";
									   		$Batterien_AR["Leer"][$l]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Leer"][$l]["ID"] = $DeviceID;
									   		$Batterien_AR["Leer"][$l]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Leer"][$l]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Leer"][$l]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$a++;
									   		$l++;
										}
										else
										{
									   		$Batterien_AR["Alle"][$a]["Name"] = IPS_GetName($InstanzID);
									   		$Batterien_AR["Alle"][$a]["Batterie"] = "OK";
									   		$Batterien_AR["Alle"][$a]["Hersteller"] = $InstanzHersteller;
									   		$Batterien_AR["Alle"][$a]["ID"] = $DeviceID;
									   		$Batterien_AR["Alle"][$a]["Hersteller_ID"] = $InstanzHersteller." - ".$DeviceID;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateTimestamp"] = $VarLastUpdated;
									   		$Batterien_AR["Alle"][$a]["LetztesVarUpdateVorSek"] = $VarLastUpdatedDiffSek;
									   		$a++;
										}
						  	}
						}
				}

				if (isset($Batterien_AR))
				{
						//Array sortieren (nach Name), doppelte Eintr�ge entfernen und neu durchnummerieren
						foreach ($Batterien_AR["Alle"] as $nr => $inhalt)
						{
							  $nameALLE[$nr] = strtolower($inhalt["Name"]);
						    $batterieALLE[$nr] = strtolower($inhalt["Batterie"]);
						    $herstellerALLE[$nr] = strtolower($inhalt["Hersteller"]);
						    $idALLE[$nr] = strtolower($inhalt["ID"]);
						    $herstelleridALLE[$nr] = strtolower($inhalt["Hersteller_ID"]);
						    $lastupdatetsALLE[$nr] = strtolower($inhalt["LetztesVarUpdateTimestamp"]);
						    $lastupdatevsALLE[$nr] = strtolower($inhalt["LetztesVarUpdateVorSek"]);
						}
						array_multisort($nameALLE, SORT_ASC, $Batterien_AR["Alle"]);
						$Batterien_AR["Alle"] = $this->Array_UniqueBySubitem_Sort($Batterien_AR["Alle"], "Hersteller_ID");
						$Batterien_AR["Alle"] = array_merge($Batterien_AR["Alle"]);
						
						foreach ($Batterien_AR["Leer"] as $nr => $inhalt)
						{
							  $nameLEER[$nr] = strtolower($inhalt["Name"]);
						    $batterieLEER[$nr] = strtolower($inhalt["Batterie"]);
						    $herstellerLEER[$nr] = strtolower($inhalt["Hersteller"]);
						    $idLEER[$nr] = strtolower($inhalt["ID"]);
						    $herstelleridLEER[$nr] = strtolower($inhalt["Hersteller_ID"]);
						    $lastupdatetsLEER[$nr] = strtolower($inhalt["LetztesVarUpdateTimestamp"]);
						    $lastupdatevsLEER[$nr] = strtolower($inhalt["LetztesVarUpdateVorSek"]);
						}
						array_multisort($nameLEER, SORT_ASC, $Batterien_AR["Leer"]);
						$Batterien_AR["Leer"] = $this->Array_UniqueBySubitem_Sort($Batterien_AR["Leer"], "Hersteller_ID");
						$Batterien_AR["Leer"] = array_merge($Batterien_AR["Leer"]);
						
						return $Batterien_AR;
				}
				else
				{
						return false;
				}
    }

		private function HTMLausgabeGenerieren($BatterienAR, $AlleLeer)
		{
				$HintergrundFarbcode = $this->ReadPropertyString("HintergrundFarbcode");
				$TextFarbcode = $this->ReadPropertyString("TextFarbcode");
				$TextFarbcodeOK = $this->ReadPropertyString("TextOKFarbcode");
				$TextFarbcodeLEER = $this->ReadPropertyString("TextLOWFarbcode");
				$TextSize = $this->ReadPropertyString("TextSize");
				$TextSizeTitle = $TextSize + 2;
				switch ($this->ReadPropertyString("TextAusrichtungDD"))
				{
						case "links":
							$Textausrichtung = "text-align:left;";
						break;
						case "mitte":
							$Textausrichtung = "text-align:center;";
						break;
						case "rechts":
							$Textausrichtung = "text-align:right;";
						break;
				}
				$HTML_CSS_Style = '<style type="text/css">
				.bt {border-collapse;border-spacing:4;}
				.bt td'.$this->InstanceID.' {font-family:Arial, sans-serif;font-size:'.$TextSize.'px;color:#'.$TextFarbcode.';padding:1px 10px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
				.bt th'.$this->InstanceID.' {font-family:Arial, sans-serif;font-size:'.$TextSize.'px;color:#'.$TextFarbcode.';padding:1px 10px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
				.bt .tb-title'.$this->InstanceID.'{font-size:'.$TextSizeTitle.'px;background-color:#'.$HintergrundFarbcode.';color:#'.$TextFarbcode.';text-align:center}
				.bt .tb-content'.$this->InstanceID.'{font-size:'.$TextSize.'px;'.$Textausrichtung.'background-color:#'.$HintergrundFarbcode.';color:#'.$TextFarbcode.';text-align:center}
				.bt .tb-contentOK'.$this->InstanceID.'{font-size:'.$TextSize.'px;'.$Textausrichtung.'background-color:#'.$HintergrundFarbcode.';color:#'.$TextFarbcodeOK.';text-align:center}
				.bt .tb-contentLOW'.$this->InstanceID.'{font-size:'.$TextSize.'px;'.$Textausrichtung.'background-color:#'.$HintergrundFarbcode.';color:#'.$TextFarbcodeLEER.';text-align:center}
				</style>';
			
				$TitelAR = array("Aktor","Hersteller","ID","Batterie","Letztes Variablen-Update");
				$HTML = '<html>'.$HTML_CSS_Style;
				$HTML .= '<table class="bt">';
				$HTML .= '<tr><th class="tb-title'.$this->InstanceID.'"><b>'.$TitelAR[0].'</b></th><th class="tb-title'.$this->InstanceID.'"><b>'.$TitelAR[1].'</b></th><th class="tb-title'.$this->InstanceID.'"><b>'.$TitelAR[2].'</b></th><th class="tb-title'.$this->InstanceID.'"><b>'.$TitelAR[3].'</b></th><th class="tb-title'.$this->InstanceID.'"><b>'.$TitelAR[4].'</b></th></tr>';
				
				if ($AlleLeer == "Alle") {
						if (isset($BatterienAR["Alle"]))
						{
								foreach ($BatterienAR["Alle"] as $Aktor)
								{
						    		if ($Aktor["Batterie"] == "OK")
										{
												$HTML .= '<tr><th class="tb-content'.$this->InstanceID.'">'.$Aktor["Name"].'</th><th class="tb-content'.$this->InstanceID.'">'.$Aktor["Hersteller"].'</th><th class="tb-content'.$this->InstanceID.'">'.$Aktor["ID"].'</th><th class="tb-contentOK'.$this->InstanceID.'">'.$Aktor["Batterie"].'</th><th class="tb-content'.$this->InstanceID.'">'.date("d.m.Y H:i", $Aktor["LetztesVarUpdateTimestamp"]).'Uhr</th></tr>';
										}
										elseif ($Aktor["Batterie"] == "LEER")
										{
												$HTML .= '<tr><th class="tb-content'.$this->InstanceID.'">'.$Aktor["Name"].'</th><th class="tb-content'.$this->InstanceID.'">'.$Aktor["Hersteller"].'</th><th class="tb-content'.$this->InstanceID.'">'.$Aktor["ID"].'</th><th class="tb-contentLOW'.$this->InstanceID.'">'.$Aktor["Batterie"].'</th><th class="tb-content'.$this->InstanceID.'">'.date("d.m.Y H:i", $Aktor["LetztesVarUpdateTimestamp"]).'Uhr</th></tr>';
										}
								}
								$HTML .= '</table></html>';
								$this->SetValueString("TabelleBatteryAlleVAR", $HTML);
						}
						else
						{
								$HTML .= '<tr><th class="tb-content'.$this->InstanceID.'" colspan="5">Keine Aktoren mit Batterien gefunden!</th></tr>';
						}
				}
				elseif ($AlleLeer == "Leer") {
						if (isset($BatterienAR["Leer"]))
						{
								foreach ($BatterienAR["Leer"] as $Aktor)
								{
										$HTML .= '<tr><th class="tb-content'.$this->InstanceID.'">'.$Aktor["Name"].'</th><th class="tb-content'.$this->InstanceID.'">'.$Aktor["Hersteller"].'</th><th class="tb-content'.$this->InstanceID.'">'.$Aktor["ID"].'</th><th class="tb-contentLOW'.$this->InstanceID.'">'.$Aktor["Batterie"].'</th><th class="tb-content'.$this->InstanceID.'">'.date("d.m.Y H:i", $Aktor["LetztesVarUpdateTimestamp"]).'Uhr</th></tr>';
								}
						}
						else
						{
								$HTML .= '<tr><th class="tb-content'.$this->InstanceID.'" colspan="5">Keine Aktoren mit leeren Batterien vorhanden!</th></tr>';
						}
						$HTML .= '</table></html>';
						$this->SetValueString("TabelleBatteryLowVAR", $HTML);
				}
		}
		
		private function Array_UniqueBySubitem_Sort($array, $key, $sort_flags = SORT_STRING)
		{
		    $items = array();
		    foreach($array as $index => $item) $items[$index] = $item[$key];
		    $uniqueItems = array_unique($items, $sort_flags);
		    return array_intersect_key($array, $uniqueItems);
		}

    private function SetValueBoolean($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueBoolean($ID) <> $Value)
        {
            SetValueBoolean($ID, boolval($Value));
            return true;
        }
        return false;
    }

    private function SetValueInteger($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueInteger($id) <> $value)
        {
            SetValueInteger($id, $value);
            return true;
        }
        return false;
    }
    
    private function SetValueString($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueString($ID) <> $Value)
        {
            SetValueString($ID, strval($Value));
            return true;
        }
        return false;
    }
    
    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 0)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }
    
    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }
    
    protected function RegisterTimer($Name, $Interval, $Script)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            $id = 0;


        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception("Ident with name " . $Name . " is used for wrong object type", E_USER_WARNING);

            if (IPS_GetEvent($id)['EventType'] <> 1)
            {
                IPS_DeleteEvent($id);
                $id = 0;
            }
        }

        if ($id == 0)
        {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $Name);
        }
        IPS_SetName($id, $Name);
        IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $Script);
        if ($Interval > 0)
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);

            IPS_SetEventActive($id, true);
        } else
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);

            IPS_SetEventActive($id, false);
        }
    }

    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception('Timer not present', E_USER_NOTICE);
            IPS_DeleteEvent($id);
        }
    }
    
    protected function SetTimerInterval($Name, $Interval)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            throw new Exception('Timer not present', E_USER_WARNING);
        if (!IPS_EventExists($id))
            throw new Exception('Timer not present', E_USER_WARNING);

        $Event = IPS_GetEvent($id);

        if ($Interval < 1)
        {
            if ($Event['EventActive'])
                IPS_SetEventActive($id, false);
        }
        else
        {
            if ($Event['CyclicTimeValue'] <> $Interval)
                IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
            if (!$Event['EventActive'])
                IPS_SetEventActive($id, true);
        }
    }
}
?>