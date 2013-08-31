/*
	Utility class for validation.
	Author: Paul Elessar W. Caceres
	Last Modified: August 31, 2013
*/

package  kiosk.extra{
	import kiosk.extra.StringHelper;
	
	public class Validator {

		public function Validator() {
			// constructor code
		}
		
		//Checks if the string contains only numbers..
		public function validateNumberOnly(string:String):Boolean{
			for(var i = 0; i < string.length; i++){
				if(string.charCodeAt(i) < 48 || string.charCodeAt(i) > 57) return false;
			}
			
			return true;
		}
		
		//Checks if the string contains only the characters from the alphabet..
		public function validateAlphaOnly(string:String):Boolean{
			for(var i = 0; i < string.length; i++){
				if(string.charCodeAt(i) < 65 || (string.charCodeAt(i) > 90 && string.charCodeAt(i) < 97) || string.charCodeAt(i) > 122) return false;
			}
			
			return true;
		}
		
		//Checks if the string contains only alphanumeric characters..
		public function validateAlphaNumericOnly(string:String):Boolean{
			for(var i = 0; i < string.length; i++){
				if(string.charCodeAt(i) < 48 || (string.charCodeAt(i) > 57 && string.charCodeAt(i) < 65) || (string.charCodeAt(i) > 90 && string.charCodeAt(i) < 97) || string.charCodeAt(i) > 122) return false;
			}
			
			return true;
		}
		
		//Validates the number of characters in the string.. The length required must be provided in the second argument..
		public function validateCount(string:String, count:int){
			if(string.length != count) return false;
			return true;
		}
	}
	
}
