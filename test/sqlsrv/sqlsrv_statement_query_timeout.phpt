--TEST--
Test sending queries (query or prepare) with a timeout specified. Errors are expected.
--FILE--
﻿<?php
include 'tools.inc';

function QueryTimeout($conn, $exec)
{
    $tableName = GetTempTableName();
    
    $stmt = sqlsrv_query($conn, "CREATE TABLE [$tableName] ([c1_int] int, [c2_tinyint] tinyint, [c3_smallint] smallint, [c4_bigint] bigint, [c5_bit] bit, [c6_float] float, [c7_real] real, [c8_decimal] decimal(28,4), [c9_numeric] numeric(32,4), [c10_money] money, [c11_smallmoney] smallmoney, [c12_char] char(512), [c13_varchar] varchar(512), [c14_varchar_max] varchar(max), [c15_uniqueidentifier] uniqueidentifier, [c16_datetime] datetime, [c17_smalldatetime] smalldatetime, [c18_timestamp] timestamp)");  
    sqlsrv_free_stmt($stmt);
    
    if ($exec)
    {
        $stmt = sqlsrv_query($conn, "WAITFOR DELAY '00:00:03'; SELECT * FROM $tableName", array(), array('QueryTimeout' => 1));
    }
    else
    {
        $stmt = sqlsrv_prepare($conn, "WAITFOR DELAY '00:00:05'; SELECT * FROM $tableName", array(), array('QueryTimeout' => 1));
        sqlsrv_execute($stmt);
    }

    $errors = sqlsrv_errors(SQLSRV_ERR_ALL); 
    $e = $errors[0];    
    
    print($e['message'] . "\n");    
    print($e['code'] . "\n");    
    print($e['SQLSTATE'] . "\n");    
   
}

function Repro()
{
    StartTest("sqlsrv_statement_query_timeout");
    try
    {
        set_time_limit(0);  
        sqlsrv_configure('WarningsReturnAsErrors', 1);  

        require_once("autonomous_setup.php");
        $database = "tempdb";
        
        // Connect
        $connectionInfo = array("UID"=>$username, "PWD"=>$password);
        $conn = sqlsrv_connect($serverName, $connectionInfo);
        if( !$conn ) { FatalError("Could not connect.\n"); }
      
        QueryTimeout($conn, true);
        QueryTimeout($conn, false);
        
        sqlsrv_close($conn);           

    }
    catch (Exception $e)
    {
        echo $e->getMessage();
    }
    echo "\nDone\n";
    EndTest("sqlsrv_statement_query_timeout");
}

Repro();

?>
--EXPECT--
﻿
...Starting 'sqlsrv_statement_query_timeout' test...
[Microsoft][ODBC Driver 13 for SQL Server]Query timeout expired
0
HYT00
[Microsoft][ODBC Driver 13 for SQL Server]Query timeout expired
0
HYT00

Done
...Test 'sqlsrv_statement_query_timeout' completed successfully.
