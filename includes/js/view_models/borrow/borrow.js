

var ViewModel = function(data){
	var self = this;
	self.initialData = data;
	self.gearArray = ko.observableArray();
	self.gear_list= ko.observableArray();
    self.selectedItems = ko.observableArray([]);
    self.postJson=ko.observable("");
	
	self.init = function() {
		self.generateArrays();
		self.refreshDatatable();
	};
 
    self.removeSelected = function () {
        self.gear_list.removeAll(self.selectedItems());
        self.selectedItems([]); // Clear selection
    };

	self.generateArrays = function(){
		for(var index in data['gear']){
			self.gearArray.push(new RecordViewModel(data['gear'][index]));
		}
	}

	self.refreshDatatable = function() {
		self.table = $("#dataTable").DataTable({
			data: self.gearArray(),
	        columns: [
	            { 	"data": function(row){
		        		return row['id'];
	            	}, 
		            title: "ID" 
		        },
		        { 	"data": function(row){
		        		return row['age'];
	            	}, 
		            title: "Age" 
		        },
		        { 	"data": function(row){
		        		return row['name'];
	            	}, 
		            title: "Name" 
		        },
		        { 	"data": function(row){
		        		return row['type'];
	            	}, 
		            title: "Type" 
		        }
	        ], 
			stateSave: true,
			dom: '<"left"l>fBrtip',
			buttons: [
	        ],
			fixedHeader: true
	        // "pagingType": "full_numbers"
		});

		$("#dataTable").on('click', 'tbody tr', function(e){
			self.itemToAdd=ko.observable(self.table.row( this ).data());
	        if ((self.itemToAdd() != "") && (self.gear_list.indexOf(self.itemToAdd()) < 0)) // Prevent blanks and duplicates
	            self.gear_list.push(self.itemToAdd());
		});
	}
}

var RecordViewModel = function(data){
	var self = this;
	// self.initialData = data;

	for(var field in data){
		var val = data[field];
		// self[field] = ko.observable(val);
		self[field] = val;
		
	}

}