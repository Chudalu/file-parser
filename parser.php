<?php
//Get current path
$currentPath = dirname(__FILE__);
echo $currentPath;
//Retrieve inputs and verify inputs
$inputArray = array_fill(0, 3, null);
 if ($argc <= 2) {
    echo "\n******\r\nException: Please enter file name and specify an output file name.\n******";
    exit();
 }
for($i = 1; $i < $argc; $i++) {
    $inputArray[$i - 1] = $argv[$i];
}
$inputFile = $inputArray[0];
$outputFilename = $inputArray[1];
$toNotPrint = $inputArray[2];

//check for different file formats
$file_parts = pathinfo($inputFile);
switch($file_parts['extension'])
{
    case "csv":
        parseCsv($currentPath, $inputFile, $outputFilename, $toNotPrint, ",");
    break;

    case "tsv":
        parseCsv($currentPath, $inputFile, $outputFilename, $toNotPrint, "\t");
    break;

    case "json":
        echo "\n******\r\njson format not available for now\n******";
    break;
    /*
    ......... more file format checks as desired
    */
    case "": 
        //Default: Handle file extension for files ending in '.' 
        echo "\n******\r\nDefaulting file type to .csv\n******";
        parseCsv($currentPath, $inputFile, $outputFilename, $toNotPrint);
    case NULL: 
        //Default: Handle no file extension
        echo "\n******\r\nDefaulting file type to .csv\n******";
        parseCsv($currentPath, $inputFile, $outputFilename, $toNotPrint);
    break;
    default:
        echo "\n******\r\nSorry your specified format not yet available yet. (parses: .csv)\n******";

}

function parseJson(){
    //function implementation to handle json formats
}

function parseTxt() {
    //function implementation to handle json formats
}

/*
......... more parse functions as desired
 */

function parseCsv($path, $file, $outputPath, $dontPrint, $delimiter) {
    //get file location
    $csvFile = $path . "\inputs\ " . $file;
    //remove spaces
    $csvFile = str_replace(' ', '', $csvFile);
    $headers = null;
    $uniqueCsvData = array();
    $uniqueCsvDataWithCount = array();
    $rowCount = 1;
    //read from CSV file and check if it exist or valid
    $fileHandler = fopen($csvFile, 'r');
    if ($fileHandler === FALSE) {
        echo "\n******\r\nSorry your file could not be found in the inputs directory.\n******";
        exit();
    }
    while (($csvData = fgetcsv($fileHandler, 1024, $delimiter)) !== FALSE) {
        if ($rowCount === 1){
            // Get CSV file headers
            $headers = $csvData;
        } else {
            //Add first row as first unique row if it does not exist
            if ((count($uniqueCsvData)>0)){
                $same = false;
                $index = 0;
                for($i=0; $i<count($uniqueCsvData); $i++){
                    //check if current row is similar to any row in the uniqueCsvData multi-dimensional array
                    $same = ( count( $uniqueCsvData[$i] ) == count( $csvData ) && !array_diff( $uniqueCsvData[$i], $csvData ) );
                    if ($same === true) {
                        $index = $i;
                    }
                }
                if ($same){
                    //increment count if it is similar to the row
                    $uniqueCsvDataWithCount[$index][count($csvData)]++;
                } else {
                    // Add to uniqueCsvData array if its not similar to any row
                    $dataWithCount = $csvData;
                    $dataWithCount[] = 1;
                    $uniqueCsvData[] = $csvData;
                    $uniqueCsvDataWithCount[] = $dataWithCount;
                }
            } else {
                //add first row
                $uniqueCsvData[] = $csvData;
                $uniqueCsvDataWithCount[] = $csvData;
                $uniqueCsvDataWithCount[0][] = 1;
            }
        }
        if ($dontPrint !== '--no-print'){
            displayProduct($rowCount-1, $headers, $csvData);
        } 
        $rowCount++;
        
    }
    createUniqueCombinationFile($headers, $uniqueCsvDataWithCount, $path, $outputPath);
}


function displayProduct($index, $propertyNames, $propertyDetails){
    echo "\n\n(". $index . ")";
    echo "\n******************************************************\n";
    for ($j=0; $j<count($propertyNames); $j++){
        echo $propertyNames[$j] . ":  '" . $propertyDetails[$j] . "' - " . $propertyNames[$j] ." name \n";
    }
    echo "\n******************************************************";
}


function createUniqueCombinationFile($fileHeaders, $uniqueCsvDataAndCount, $outFilePath, $outFile){
    $outputFile = $outFilePath . "\outputs\ " . $outFile .".csv";
    $outputFile = str_replace(' ', '', $outputFile);
    $csvHeaders = array();
    foreach ($fileHeaders as $value) {
        $csvHeaders[] = $value;
    }
    $csvHeaders[] = "Count";
    $fh = fopen($outputFile, "w");
    fputcsv($fh, $csvHeaders);
    foreach ($uniqueCsvDataAndCount as $v) {
        fputcsv($fh, $v);
    }
    fclose($fh);
}


?>