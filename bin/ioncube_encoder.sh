#!/bin/sh

encoder=""
fullEncoder="Current (v10.2)"
language=""
selectedArch=""
sysArch=""
encoderOptions=""
encoderPath=""
warning=""
isFreeBSD="" # Whether the script is running on FreeBSD which does not have any 64-bit encoders.
currentV="10.2"
legacyV="9.0"
obsoleteV="8.3"

# Encoder-Arch-Language
encodersForV10="C-64-5.3 C-64-5.4 C-64-5.5 C-64-5.6 C-64-7.1 C-64-7.2
               C-32-4 C-32-5 C-32-5.3 C-32-5.4 C-32-5.5 C-32-5.6 C-32-7.1 C-32-7.2
               L-32-4 L-32-5 L-32-5.3 L-32-5.4 L-32-5.5 L-32-5.6
               L-64-5.3 L-64-5.4 L-64-5.5 L-64-5.6
               O-32-4 O-32-5 O-32-53 O-32-54 O-32-55
               O-64-5.3 O-64-5.4 O-64-5.5"

fail()  {
    echo $*
    exit 1
}

checkSelectionCompatibility() {
    
    var="$encoder-$selectedArch-$language"
    #echo "$var"

    
    # check if selected encoder, arch and language combination is valid.
    for validEncoder in $encodersForV10; do
        [ "$validEncoder" = "$var" ] && echo valid & return
    done


    #else it is invalid.
    echo invalid
}

checkSystemCompatibility() {
    
    # check for 64-bit Encoder running on FreeBSD which does not have any 64-bit encoders.
    [ "$selectedArch" = "64" ] && [ "$isFreeBSD" = 1 ] && fail "64-bit ionCube Encoders do not exist for FreeBSD. Please use the 32-bit Encoders." 

    # check for 64-bit Encoder running on a 32-bit system.
    [ "$selectedArch" = "64" ] && [ "$sysArch" = "32" ] && fail "You cannot run the 64-bit ionCube Encoder on a 32-bit system." 

    # check for 32-bit Encoder running on a 64-bit system.
    [ "$selectedArch" = "32" ] && [ "$sysArch" = "64" ] && warning="Warning: you are running the 32-bit ionCube Encoder on a 64-bit system."
}

setEncoder() {
	if [ "$encoder" = "" ] ; then
	    encoder="$1"
        [ "$1" = "C" ] && fullEncoder="Current (v10.2)"
        [ "$1" = "L" ] && fullEncoder="Legacy (v9.0)"
        [ "$1" = "O" ] && fullEncoder="Obsolete (v8.3)"
    else
        fail "You cannot set more than one Encoder version."
    fi
}		

setLanguage() {

    if [ "$language" = "" ] ; then 
        language="$1"
    else
        fail "You cannot set more than one Encoding language."
    fi
}

setArch() {

    if [ "$selectedArch" = "" ] ; then
        selectedArch="$1"
    else 
        fail "You cannot set more than one architecture type"
    fi
}

setSysArch() {

    localArch=`uname -m`
    isFreeBSD=`uname -s | grep -ic "FreeBSD"`
    sysArch="32"

    if [ "$isFreeBSD" = "1" ]
    then
        # AJT FreeBSD only has 32-bit Encoders presently.
        sysArch="32"
    else 

        #echo "$localArch"    

        case "$localArch" in 
            "i686" | "i386")
                sysArch="32"
                ;;

            "x86_64" | "amd64")
                sysArch="64"
                ;;
        esac
    fi

}

checkLanguage() {
    if [ "$language" = "" ] ; then
        echo notSet 
    else
        echo set
    fi
}

printHelp32() {
    echo "
The following is a summary of command options for this script and its basic usage. 

Usage: ioncube_encoder.sh [-C | -L | -O] [-4 | -5 | -53 | -54 | -55 | -56 | -71 | -72 ] [-x86] <encoder options>

Encoder Version (optional):
-O : Use Obsolete Encoder (v8.3)
-L : Use Legacy Encoder (v9.0)
-C : Use Current Encoder (v10.2) - Default

PHP Languages:
-4 : Encode file in PHP 4
-5 : Encode file in PHP 5
-53 : Encode file in PHP 53
-54 : Encode file in PHP 54
-55 : Encode file in PHP 55
-56 : Encode file in PHP 56
-71 : Encode file in PHP 71
-72 : Encode file in PHP 72

Architecture (optional):
-x86 : Run the 32-bit Encoder

-h : Display this help and exit. 
If -h is specified before a language has been selected, help will be displayed by the script.
if -h is specified after a language has been selected, help will be displayed by the Encoder.

If an Encoder version is not selected, the Current Encoder (10.2) will be selected.
If a PHP language is not selected, the script will exit.
If an architecture is not selected, the script will run the Encoder that matches your system architecture.

Once an unknown option is selected, the script will pass the remaining options to the Encoder.
You cannot select more than one Encoder version, PHP language or Architecture.

Usage examples:

Current Encoder, encoded in PHP 7.1
  ./ioncube_encoder.sh -C -71 source_file.php -o target_file.php

Current Encoder, encoded in PHP 5.6. Encoder displays help.
  ./ioncube_encoder.sh -C -56 -h

Legacy Encoder, encoded in PHP 5.3
  ./ioncube_encoder.sh -L -53 
"

    exit
}

printHelp64() {
    echo "
The following is a summary of command options for this script and its basic usage. 

Usage: ioncube_encoder.sh [-C | -L | -O] [-4 | -5 | -53 | -54 | -55 | -56 | -71 | -72 ] [-x86 | -x86-64] <encoder options>

Encoder Version (optional):
-O : Use Obsolete Encoder (v8.3)
-L : Use Legacy Encoder (v9.0)
-C : Use Current Encoder (v10.2) - Default

PHP Languages:
-4 : Encode file in PHP 4
-5 : Encode file in PHP 5
-53 : Encode file in PHP 53
-54 : Encode file in PHP 54
-55 : Encode file in PHP 55
-56 : Encode file in PHP 56
-71 : Encode file in PHP 71
-71 : Encode file in PHP 72

Architecture (optional):
-x86 : Run the 32-bit Encoder
-x86-64 : Run the 64-bit Encoder

-h : Display this help and exit. 
If -h is specified before a language has been selected, help will be displayed by the script.
if -h is specified after a language has been selected, help will be displayed by the Encoder.

If an Encoder version is not selected, the Current Encoder (10.2) will be selected.
If a PHP language is not selected, the script will exit.
If an architecture is not selected, the script will run the Encoder that matches your system architecture.

Once an unknown option is selected, the script will pass the remaining options to the Encoder.
You cannot select more than one Encoder version, PHP language or Architecture.
Script will exit should you try to run the 64-bit Encoder on a 32-bit system.

Usage examples:

Current 64-bit Encoder, encoded in PHP 7.1
  ./ioncube_encoder.sh -C -x86-64 -71 source_file.php -o target_file.php

Current 64-bit Encoder, encoded in PHP 5.6. Encoder displays help.
  ./ioncube_encoder.sh -C -x86-64 -56 -h

Legacy 32-bit Encoder, encoded in PHP 5.3
  ./ioncube_encoder.sh -L -x86 -53 
"

    exit
}

printHelp() {
    if [ "$sysArch" = "64" ]
    then
        printHelp64
    else
        printHelp32
    fi
}

setEncoderFilePath() {
    # AJT 20150423 Change so that all executables go in a bin directory but are suffixed with version number and, if 64 bit, "_64".

    encoderPath=`cd \`dirname $0\` ; pwd`/bin/

    case "$language" in 
        4)
            encoderPath="$encoderPath""ioncube_encoder4"
            ;;

        5)
            encoderPath="$encoderPath""ioncube_encoder5"
            ;;

        5.3)
            encoderPath="$encoderPath""ioncube_encoder53"
            ;;

        5.4)
            encoderPath="$encoderPath""ioncube_encoder54"
            ;;

        5.5)
            encoderPath="$encoderPath""ioncube_encoder55"
            ;;

        5.6)
            encoderPath="$encoderPath""ioncube_encoder56"
            ;;

        7.1)
            encoderPath="$encoderPath""ioncube_encoder71"
            ;;

        7.2)
            encoderPath="$encoderPath""ioncube_encoder72"
            ;;
    esac

    case "$encoder" in 
        C)
            encoderPath="$encoderPath""_""$currentV"
            ;;

        L)
            encoderPath="$encoderPath""_""$legacyV"
            ;;

        O)
            encoderPath="$encoderPath""_""$obsoleteV"
            ;;
    esac


    # AJT 20150423 Now just suffix 64-bit with "_64".
    case "$selectedArch" in
        32)
            encoderPath="$encoderPath"
            ;;
        64)
            encoderPath="$encoderPath""_64"
            ;;
    esac
}

checkEncoderExists() {
    
    if [ -f $encoderPath ] ; then
        if [ -x $encoderPath ] ; then
            true
        else 
            fail "The Encoder is not executable."
        fi
    else 
        fail "The Encoder does not exist at the path: $encoderPath"
    fi
}

setSysArch

if [ $# -eq 0 ] ; then
    printHelp
fi

#while [ "$1" ]
for var in "$@" 
do
    case "$var" in
	-L)
	    setEncoder "L"
   	    ;;

   	-O)	
    	    setEncoder "O"
	    ;;

   	-C)
    	    setEncoder "C"
	    ;;

        -4)
            setLanguage "4"
            ;;

        -5)
            setLanguage "5"
            ;;

        -53)
            setLanguage "5.3"
            ;;

        -54)
            setLanguage "5.4"
            ;;

        -55) 
            setLanguage "5.5"
            ;;

        -56) 
            setLanguage "5.6"
            ;;

        -71) 
            setLanguage "7.1"
            ;;

        -72) 
            setLanguage "7.2"
            ;;

        -x86)
            setArch "32"
            ;;

        -x86-64)
            setArch "64"
            ;;

        -h)
            if [ "$language" ] ; then
                break
            else 
                printHelp
            fi;
            ;;
        
        *)
            break;
            ;;
            
    esac
    shift
done

[ "$language" = "" ] && setLanguage "7.1"
[ "$encoder" = "" ] && encoder="C" 
[ "$selectedArch" = "" ] && selectedArch="$sysArch"


#checkSelectionCompatibility
selection="$(checkSelectionCompatibility)"

if [ "$selection" = "valid" ] ; then 
    true 
elif [ "$selection" = "invalid" ] ; then
    fail "The PHP language ($language) you have selected is not supported by the $fullEncoder $selectedArch-bit ionCube Encoder. Enter -h for help."
fi


checkSystemCompatibility


#echo "$encoder $language $selectedArch"    

setEncoderFilePath

#echo "encoder options: $@"
#echo "path: $encoderPath"


checkEncoderExists

[ "$warning" != "" ] && echo "$warning"

exec $encoderPath "$@"
