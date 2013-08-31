/* Taken from Flash CS4 Professional ActionScript 3.0 Language Reference*/
package kiosk.extra{
	public class StringHelper {
		public function StringHelper() {
		}
	
		public function replace(str:String, oldSubStr:String, newSubStr:String):String {
			return str.split(oldSubStr).join(newSubStr);
		}
	
		public function trim(str:String):String {
			return trimBack(trimFront(str));
		}
	
		public function trimFront(str:String):String {
			var pattern:RegExp = new RegExp("\s");
			if (str.charAt(0).charCodeAt(0) == 10 || str.charAt(0).charCodeAt(0) == 13 || str.charAt(0).charCodeAt(0) == 27 || str.charAt(0).charCodeAt(0) == 32) {
				str = trimFront(str.substring(1));
			}
			return str;
		}
	
		public function trimBack(str:String):String {
			if (str.charAt(str.length - 1).charCodeAt(0) == 10 || str.charAt(str.length - 1).charCodeAt(0) == 13 || str.charAt(str.length - 1).charCodeAt(0) == 27 || str.charAt(str.length - 1).charCodeAt(0) == 32) {
				str = trimBack(str.substring(0, str.length - 1));
			}
			
			return str;
		}
	
		public function stringToCharacter(str:String):String {
			if (str.length == 1) {
				return str;
			}
			return str.slice(0, 1);
		}
	}
}