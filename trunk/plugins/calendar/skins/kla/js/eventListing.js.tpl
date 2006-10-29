/*JS file*/

var events = new Array ();
/*fill events*/
{foreach from=$Calendar_Events item='event'}
	var eventData = new Array ();
	var groupData = new Array ();
	groupData['Name'] = "{$event.group.Name}";
	groupData['Color'] = "{$event.group.Color}";
	eventData['Title'] = "{$event.Title}";
	eventData['StartDate'] = "{$event.StartDate|date_format:"%d/%m %H:%m"}";
	eventData['EndDate'] = "{$event.EndDate|date_format:"%d/%m %H:%m"}";
	eventData['Description'] = "{$event.Description|nl2br|strip}";
	eventData['Group'] = groupData;
	events[{$event.ID}] = eventData;
{/foreach}

{literal}
function showEvent (ID) {
	eventBox = document.getElementById ('eventBox');
	while (eventBox.firstChild) {
		eventBox.removeChild (eventBox.firstChild);
	}
	var dateBox = document.createElement ('span');
	dateBox.setAttribute ('class', 'date');
	var nameBox = document.createElement ('span');
	nameBox.setAttribute ('class', 'name');
	var descBox = document.createElement ('p');
	descBox.setAttribute ('class', 'desc');
	
	var date = document.createTextNode (events[ID]['StartDate'] + " -- " + events[ID]['EndDate'] + "  ");
	dateBox.appendChild (date);
	
	var name = document.createTextNode (events[ID]['Title']);
	nameBox.appendChild (name);
	
	descBox.innerHTML = events[ID]['Description']; // to prevent showing up <br /> tags
	
	eventBox.appendChild (dateBox);
	eventBox.appendChild (nameBox);
	eventBox.appendChild (descBox);
}
{/literal}