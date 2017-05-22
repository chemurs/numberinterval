		
		function getDivPrefix() {
			return "interval_";
		}
		
		//return the index of the last visible <div> element
	    function getLastInterval(fieldPrefix, intervalCount) {
		    var i = 1;
		    var divPrefix = getDivPrefix();
		    for (;i<=intervalCount;i++) {
			    var currInterval = document.getElementById(fieldPrefix+divPrefix+i);
			    if (currInterval.style.display == "none")
				    break;
		    }
		    return (i-1);
	    }

	   // add a new interval - make tje next <div> element visible
	   function addInterval(fieldPrefix, intervalCount) {
			var interval = getLastInterval(fieldPrefix, intervalCount);
			if (interval == intervalCount)
				return;
			// if there was only one <div> element we must make visible the "removeButton"
			if (interval == 1) {
				var removeButton = document.getElementById(fieldPrefix+"removeButton");
				removeButton.style.display = "";
			}
			var divPrefix = getDivPrefix();
			// make the conjunction span from the currently last <div> visible
			var prevInterval = document.getElementById(fieldPrefix+divPrefix+interval);
			var conjSpan = prevInterval.lastChild;
			conjSpan.style.display = "";
			
			interval++;
			// make the next <div> element visible
			var newDiv = document.getElementById(fieldPrefix+divPrefix+interval);
			newDiv.style.display = "";
			// if we have reached the maximum div count, we must hide the "addButton"
			if (interval == intervalCount) {
				var addButton = document.getElementById(fieldPrefix+"addButton");
				addButton.style.display = "none";
			}
	   }
	   
	    // selectElement - a <select> DOM element
		// the function sets its first option selected
	   function unsetSelectElement(selectElement) {
			var options = selectElement.getElementsByTagName('option');
			var length = options.length;
			// loop through select options
			while(length--) {
				var currOption = options[length];
				// if <option> has attribute "selected", then it must below
				// selected='true', therefore remove it
				if(currOption.selected) {
					currOption.removeAttribute("selected")
				}
			}
			options[0].setAttribute("selected", true);
	   }
	   
	    // remove an interval - hide the last visible <div> element 
	   function removeInterval(fieldPrefix, intervalCount) {
		    var interval = getLastInterval(fieldPrefix, intervalCount);
			var divPrefix = getDivPrefix();
			var lastDiv = document.getElementById(fieldPrefix+divPrefix+interval);
			// hide the last <div> element
			lastDiv.style.display = "none";
			// reset the hidable <select> control values
			var hidableSelects = lastDiv.getElementsByTagName("select");
			unsetSelectElement(hidableSelects[0]);
			unsetSelectElement(hidableSelects[1]);
			// remove the conjunction span from previous <div> -
			// the <div> that will be the last one now
			var conjSpan = lastDiv.previousSibling.lastChild;
			conjSpan.style.display = "none";
			// if the <div> we hid was the last of possible <div>s, we have
			// to make "addButton" visible again
			if (interval == intervalCount) {
				var addButton = document.getElementById(fieldPrefix+"addButton");
				addButton.style.display = "";
			}
			interval--;
			// if there is only one <div> element left we must hide the "removeButton"
			if (interval == 1) {
				var removeButton = document.getElementById(fieldPrefix+"removeButton");
				removeButton.style.display = "none";
			}
	   }
	