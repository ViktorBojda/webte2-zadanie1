$(function() {
    $("#table-winners").DataTable(
        {
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "order": [],
            "columnDefs": 
            [
                { 
                    "orderable": false,
                    "targets": [1, 6]  
                },
                {
                    "target": 0,
                    "visible": false,
                    "searchable": false
                }
            ],
            "createdRow": function( row, data, dataIndex ) {
                let $fullName = $(row).find('td:eq(0), td:eq(1)');
                $fullName.on("click", function() {
                    location.href = `./detail.php?id=${data[0]}`;
                });
                }
        }
    );
});