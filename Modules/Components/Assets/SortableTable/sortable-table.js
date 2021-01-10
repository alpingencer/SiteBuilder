
// Define sort function
var sortTable = function (table, sortColumn, reverse) {
	// Get <tbody> in <table>
	var tbody = table.tBodies[0];
	
	// Split <tr> in <tbody> into array
	var rows = Array.prototype.slice.call(tbody.rows);
	
	// Take inverse of reverse
	reverse = -((+reverse) || -1);
	
	// Sort rows based on inner html
	rows.sort(function(a, b) {
		var t1 = a.cells[sortColumn].innerHTML;
		var t2 = b.cells[sortColumn].innerHTML;
		return t1.localeCompare(t2) * reverse;
	});
	
	// Append rows back into <tbody>
	for(var i = 0; i < rows.length; i++) {
		tbody.appendChild(rows[i]);
	}
}

// Loop through each .sitebuilder-list-table on the page
Array.from(document.getElementsByClassName('sitebuilder-sortable-table')).forEach(function(table) {
	// Loop through each <th> in <thead> in <table>
	Array.from(table.tHead.rows[0].cells).forEach(function(th, index) {
		var sortDir = 1;
		th.onclick = function() {
			// Reverse sort order and sort on click
			sortDir = 1 - sortDir;
			sortTable(table, index, sortDir);
		};
	});
});
