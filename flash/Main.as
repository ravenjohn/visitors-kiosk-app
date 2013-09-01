/*
	Author: Paul Elessar W. Caceres
	Last Modified: Sept. 1, 2013
*/

package {
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.events.Event;
	import flash.display.StageDisplayState;
	import kiosk.extra.StringHelper;
	import kiosk.extra.Validator;
	import flash.net.URLRequest;
	import flash.net.URLLoader;
	import flash.net.URLRequestMethod;
	import flash.display.Loader;
	import flash.geom.Point;
	import flash.net.URLVariables;
	import flash.net.URLLoaderDataFormat;
	import flash.net.navigateToURL;
	
	public class Main extends MovieClip {
		var objectState:String;
		var stringHelper:StringHelper;
		var errorBox:ErrorBox;
		var infoBox:InfoBox;
		var validator:Validator;
		var avatar:String;
		var xmlRequest:URLRequest;
		var xmlLoader:URLLoader;
		var xmlFile:XML;
		var imageRequest:URLRequest;
		var flagImage:Loader;
		var infoAdded:Boolean;
		
		public function Main() {
			// constructor code
			//stage.displayState = StageDisplayState.FULL_SCREEN_INTERACTIVE;
			stringHelper = new StringHelper();
			errorBox = new ErrorBox();
			infoBox = new InfoBox();
			validator = new Validator();
			infoAdded = false;
			
			this.addEventListener(MouseEvent.CLICK, onClick);
			this.addEventListener(Event.ENTER_FRAME, onEverything);
		}
		
		//Load the necessary XML file..
		private function loadXML(fileURL:String):void{
			xmlRequest = new URLRequest(fileURL);
			xmlLoader = new URLLoader(xmlRequest);
			xmlLoader.addEventListener(Event.COMPLETE, onXMLLoaded);
		}
		
		private function onXMLLoaded(e:Event):void{
			xmlFile = new XML(e.target.data);
		}
		
		private function loadFlag(fileURL:String):void{
			flagImage = null;
			imageRequest = new URLRequest(fileURL);
			flagImage = new Loader();
			flagImage.load(imageRequest);
			flagImage.contentLoaderInfo.addEventListener(Event.COMPLETE, onFlagLoaded);
		}
		
		private function onFlagLoaded(e:Event):void{
			flagImage.width = visitorPage.avatar.flag.width;
			flagImage.height = visitorPage.avatar.flag.height;
			flagImage.x = visitorPage.avatar.flag.localToGlobal(new Point(visitorPage.avatar.flag.x, visitorPage.avatar.flag.y)).x-(visitorPage.avatar.flag.width/2);
			flagImage.y = visitorPage.avatar.flag.localToGlobal(new Point(visitorPage.avatar.flag.x, visitorPage.avatar.flag.y)).y+visitorPage.avatar.flag.height;
			
			addChild(flagImage);
		}
		
		public function onClick(e:MouseEvent):void{
			if(e.target.name == "irriButton"){
				gotoAndStop(3);
			}
			else if(e.target.name == "visitorButton"){
				gotoAndStop(2);
			}
			else if(e.target.name == "homeButton"){
				gotoAndStop(1);
			}
			else if(e.target.name == "loginButton"){
				if(validateInput()){
					sendData();
					gotoAndStop(3);
				}
			}
			else if(e.target.name == "nextButton"){
				irriPage.play();
			}
			else if(e.target.name == "adminButton"){
				
			}
			else if(e.target.name == "nameInput" || e.target.name == "affiliationInput" || e.target.name == "numberInput"){
				//Make sure to remove only the default values..
				if(stringHelper.trim(e.target.text.toLowerCase()) == "name" || stringHelper.trim(e.target.text.toLowerCase()) == "affiliation" || stringHelper.trim(e.target.text.toLowerCase()) == "contact number"){
					e.target.text = "";
				}
			}
			else if(e.target.name == "errorOkButton"){
				removeChild(e.target.parent);
			}
			else if(e.target.name == "infoOkButton"){
				irriPage.infoBox.visible = false;
			}
		}
		
		public function onCountryChange(e:Event):void{
			var country = visitorPage.form.countryInput.selectedItem.data.toString();
			loadFlag("files/images/flags/"+country.toLowerCase()+".png");
		}
		
		public function onEverything(e:Event):void{
			if(currentFrame == 2){
				visitorPage.form.countryInput.addEventListener(Event.CHANGE, onCountryChange);
			}
		}
		
		private function sendData():void{
			var visitorName = stringHelper.trim(visitorPage.form.nameInput.text);
			
			var url = "/users/visit";
			url = url+"?name="+stringHelper.trim(visitorPage.form.nameInput.text)+"&affiliation="+stringHelper.trim(visitorPage.form.affiliationInput.text)+"&country="+visitorPage.form.countryInput.selectedItem.data+"&category="+visitorPage.form.categoryInput.selectedItem.data+"&contact="+stringHelper.trim(visitorPage.form.numberInput.text);
			var urlRequest:URLRequest = new URLRequest(url);
			var urlLoader:URLLoader = new URLLoader();
			
			urlRequest.method = URLRequestMethod.GET;
			
			try{
				urlLoader.load(urlRequest);
			}catch(e:Error){
				trace(e);
			}
		}
		
		public function validateInput():Boolean{
			var visitorName = stringHelper.trim(visitorPage.form.nameInput.text);
			var affiliation = stringHelper.trim(visitorPage.form.affiliationInput.text);
			var contactNumber = stringHelper.trim(visitorPage.form.numberInput.text);
			
			//Check if the text fields are empty..
			errorBox.textContent.text = "Please fill out all of the fields.";
			if(visitorName == ""){
				addChild(errorBox);
				return false;
			}
			else if(affiliation == ""){
				addChild(errorBox);
				return false;
			}
			else if(contactNumber == ""){
				addChild(errorBox);
				return false;
			}
			
			//Check if contact number really contains a number..
			errorBox.textContent.text = "The contact number must consist of digits only.";
			if(!validator.validateNumberOnly(contactNumber)){
				addChild(errorBox);
				return false;
			}
			
			//Check if the length of the contact number is either 7 or 11..
			errorBox.textContent.text = "The contact number is of the wrong length. It's currently "+contactNumber.length+" character(s) long.";
			if(!validator.validateCount(contactNumber, 7) && !validator.validateCount(contactNumber, 11)){
				addChild(errorBox);
				return false;
			}
			
			//If there are no errors, return true..
			return true;
		}
	}
}
