function checkField(sField,sValue,bRequired,iMin,iMax)
{
	var strError = "";
	var sStrip = sValue;
	sStrip = sStrip.replace(/ /g, "");
	if (sStrip.length == 0 && bRequired)
		return "\t" + sField + " field is required\n";
	if (sValue.length > 0)
	{
		if (iMin > 0 && sValue.length < iMin)
			strError += "\t" + sField + " field should have min. " + iMin + " chars\n";
		else if (iMax > 0 && sValue.length > iMax)
			strError += "\t" + sField + " field should have max. " + iMax + " chars\n";
	}
	return strError;
}
function checkSelectField(sField,iIndex)
{
	if (iIndex == 0)
		return "\t" + sField + " field is required\n";
	return "";
}
function checkEmptyValue(sField,value)
{
	if (value == "")
		return "\t" + sField + " field is required\n";
	return "";
}
function checkEmptyValue3(sField,value1,value2,value3)
{
	if (value1 == "" || value2 == "" || value3 == "")
		return "\t" + sField + " field is required\n";
	return "";
}
function checkIntField(sField,iValue,bRequired,iMin,iMax)
{
	if (iValue=="")
	{
		if (bRequired)
			return "\t" + sField + " field is required\n";
		else
			return "";
	}
	if (isNaN(iValue))
		return "\t" + sField + " field should be Numeric\n";
	if (parseInt(iValue) != iValue)
		return "\t" + sField + " field should be Integer\n";
	if (! isNaN(iMin))
		if (parseInt(iValue) < parseInt(iMin))
			return "\t" + sField + " field should not be less than " + iMin + "\n";
	if (! isNaN(iMax))
		if (parseInt(iValue) > parseInt(iMax))
			return "\t" + sField + " field should not be more than " + iMax + "\n";
	return "";
}
function checkFloatField(sField,iValue,bRequired,iMin,iMax)
{
	if (iValue=="")
	{
		if (bRequired)
			return "\t" + sField + " field is required\n";
		else
			return "";
	}
	if (isNaN(iValue))
		return "\t" + sField + " field should be Numeric\n";
	if (! isNaN(iMin))
		if (parseFloat(iValue) < parseFloat(iMin))
			return "\t" + sField + " field should not be less than " + iMin + "\n";
	if (! isNaN(iMax))
		if (parseFloat(iValue) > parseFloat(iMax))
			return "\t" + sField + " field should not be more than " + iMax + "\n";
	return "";
}

function checkCheckField(sField,objForm,iCount)
{
	var strError = "";
	var i;
	var bCheck;
	if (iCount == '1') {
		if (objForm.checked)  bCheck = true;
	}
	else{
		for(i=0; i<iCount; i++){
			if (objForm[i].checked)  bCheck = true;
		}
	}
	if (!bCheck) strError=strError += "\t" + sField + " field is required\n";
	return strError;
}
