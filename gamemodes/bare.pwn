#include <a_samp.inc>
#include <a_mysql.inc>

#define function%0(%1) forward%0(%1); public%0(%1)
#define SECONDS(%1) ((%1)*(1000))

// SQL .Server --
#define sqlhost "127.0.0.1"
#define sqluser "root"
#define sqlpass ""
#define sqldb   "settingspanel"
#define sqlport 3322 // 3306
static SQL;
// SQL .Server --

new gWeatherServer = 0;

main()
{
	SQL = mysql_connect(sqlhost, sqluser, sqldb, sqlpass, sqlport);
	if(mysql_errno(SQL)) print("*_* The connection to the host was not successfull."); // Un Load SQL-MySql .Server --
	else print("*/ The connection to the host was successfull."); // Load SQL-MySql .Server --
}

public OnGameModeInit() {
    SetTimer("WeatherServerCheck", SECONDS(60), true); // Reload 1-MINUTE --
	return true;
}

function WeatherServerCheck() {
    mysql_tquery(SQL, "SELECT gWeather FROM others WHERE id = 1", "OnShowResult", "");
    
    SetWeather(gWeatherServer);
	return true;
}
function OnShowResult() {
    if(cache_num_rows() > 0) {
        new val;
        gWeatherServer = cache_get_field_content_int(0, "gWeather", val);
    }
}
