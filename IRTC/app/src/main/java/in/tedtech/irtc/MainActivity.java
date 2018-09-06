package in.tedtech.irtc;

import android.Manifest;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Criteria;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.Uri;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.design.widget.BottomNavigationView;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.NotificationCompat;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.MenuItem;
import android.view.View;
import android.webkit.WebView;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;
import java.sql.Timestamp;
import java.util.Date;
import org.json.JSONObject;
import java.io.DataOutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Random;



public class MainActivity extends AppCompatActivity implements LocationListener{



    private Button button;
    private TextView textView;
    private LocationListener listener;
    LocationManager locationManager;
    String mprovider;
    public Button bt;
    private WebView wv;
    public String current_location = "";
    // This Id can be fetched using cognito
    public int user_id = 1;
    public void onLocationChanged(Location location) {
        TextView longitude = (TextView) findViewById(R.id.textView);
        TextView latitude = (TextView) findViewById(R.id.message);
        Random rand = new Random();
        current_location = location.getLatitude()+","+location.getLongitude();
        longitude.setText("Current Location : " +current_location);
        latitude.setText("Current Latitude:" + location.getLatitude());

        sendPost(current_location, rand.nextInt(99999), 0);
        sendLiveLocation(current_location, user_id, 0 );
    }

    public void onStatusChanged(String s, int i, Bundle bundle) {

    }

    public void onProviderEnabled(String s) {

    }

    public void onProviderDisabled(String s) {

    }
    private TextView mTextMessage;

    // Amazon api gateway URL for sendLocation
    final String urlAddress = "https://0hlla8ccid.execute-api.us-east-2.amazonaws.com/irtc_api_deploy/irtc-resource";
    final String liveUrlAddress = "https://8fxls0k705.execute-api.us-east-2.amazonaws.com/deploy_live";
    private BottomNavigationView.OnNavigationItemSelectedListener mOnNavigationItemSelectedListener
            = new BottomNavigationView.OnNavigationItemSelectedListener() {

        @Override
        public boolean onNavigationItemSelected(@NonNull MenuItem item) {
            try{
                    bt = (Button) findViewById(R.id.button1);
                    wv = findViewById(R.id.webView);
            }catch(Exception e){}
            switch (item.getItemId()) {
                case R.id.navigation_home:
                    mTextMessage.setText(R.string.title_home);
                    //sendPost("xyz"+","+"abc", 44);
                    //Toast.makeText(MainActivity.this, "Post Sent", Toast.LENGTH_SHORT).show();
                    /*
                    */
                    bt.setVisibility(View.VISIBLE);
                    wv.setVisibility(View.INVISIBLE);
                    return true;
                case R.id.navigation_dashboard:
                    mTextMessage.setText(R.string.title_dashboard);
                    bt.setVisibility(View.INVISIBLE);
                    wv.setVisibility(View.VISIBLE);
                    return true;
                case R.id.navigation_notifications:
                    bt.setVisibility(View.INVISIBLE);
                    wv.setVisibility(View.INVISIBLE);
                    mTextMessage.setText(R.string.title_notifications);

                    return true;
            }
            return false;
        }
    };


    @Override
    protected void onCreate(Bundle savedInstanceState) {

        WebView wv = findViewById(R.id.webView);

        textView = (TextView) findViewById(R.id.textView);
        Random rand = new Random();
        locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
        Criteria criteria = new Criteria();
        mprovider = locationManager.getBestProvider(criteria, false);

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        //sendPost("hell", 123);
        mTextMessage = (TextView) findViewById(R.id.message);
        BottomNavigationView navigation = (BottomNavigationView) findViewById(R.id.navigation);
        navigation.setOnNavigationItemSelectedListener(mOnNavigationItemSelectedListener);
        LocationManager locationManager = (LocationManager)
                getSystemService(Context.LOCATION_SERVICE);

        try {
            wv.loadUrl("http://hetvi.hol.es");
            wv.getSettings().setJavaScriptEnabled(true);
            wv.setVisibility(View.INVISIBLE);

        }catch (Exception e){}


      //  sendPost("He", 12);
        if(mprovider != null && !mprovider.equals("")) {
            if(ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
                return;
            }
            Location location = locationManager.getLastKnownLocation(mprovider);
            locationManager.requestLocationUpdates(mprovider, 15000, 1, this);
            //sendPost(location.getLatitude()+","+location.getLongitude(), 11);
            if (location != null)
                onLocationChanged(location);
            else
                Toast.makeText(getBaseContext(), "No Location Provider Found Check Your Code", Toast.LENGTH_SHORT).show();
        }
        wv = (WebView)findViewById(R.id.webView);
        wv.loadUrl("http://hetvi.hol.es/aws");

    } //end of On Create





    // Code to send Post request
    public void sendPost(final String location, final double irtc, final int status) {


        Thread thread = new Thread(new Runnable() {
            Date date= new Date();
            long time = date.getTime();
            Timestamp ts = new Timestamp(time);
            @Override
            public void run() {
                try {

                    URL url = new URL(urlAddress);
                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                    conn.setRequestMethod("POST");
                    conn.setRequestProperty("Content-Type", "application/json;charset=UTF-8");
                    conn.setRequestProperty("Accept","application/json");
                    conn.setDoOutput(true);
                    conn.setDoInput(true);

                    JSONObject jsonParam = new JSONObject();
                    jsonParam.put("location", location);
                    jsonParam.put("irtc_id", irtc);
                    jsonParam.put("timestamp", ts.toString());
                    jsonParam.put("status", status);

                    Log.i("JSON", jsonParam.toString());
                    DataOutputStream os = new DataOutputStream(conn.getOutputStream());

                    //os.writeBytes(URLEncoder.encode(jsonParam.toString(), "UTF-8"));
                    os.writeBytes(jsonParam.toString());

                    os.flush();
                    os.close();

                    Log.i("STATUS", String.valueOf(conn.getResponseCode()));
                    Log.i("MSG" , conn.getResponseMessage());

                    conn.disconnect();


                } catch (Exception e) {
                    e.printStackTrace();
                }

            }
        });

        thread.start();
    }

    // Code to send Post request for Live location
    public void sendLiveLocation(final String location, final double irtc, final int status) {


        Thread thread = new Thread(new Runnable() {
            Date date= new Date();
            long time = date.getTime();
            Timestamp ts = new Timestamp(time);
            @Override
            public void run() {
                try {

                    URL url = new URL(liveUrlAddress);
                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                    conn.setRequestMethod("POST");
                    conn.setRequestProperty("Content-Type", "application/json;charset=UTF-8");
                    conn.setRequestProperty("Accept","application/json");
                    conn.setDoOutput(true);
                    conn.setDoInput(true);

                    JSONObject jsonParam = new JSONObject();
                    jsonParam.put("current_location", location);
                    jsonParam.put("user_id", irtc);
                    jsonParam.put("timestamp", ts.toString());
                    jsonParam.put("status", status);

                    Log.i("JSON", jsonParam.toString());
                    DataOutputStream os = new DataOutputStream(conn.getOutputStream());

                    //os.writeBytes(URLEncoder.encode(jsonParam.toString(), "UTF-8"));
                    os.writeBytes(jsonParam.toString());

                    os.flush();
                    os.close();

                    Log.i("STATUS", String.valueOf(conn.getResponseCode()));
                    Log.i("MSG" , conn.getResponseMessage());

                    conn.disconnect();


                } catch (Exception e) {
                    e.printStackTrace();
                }

            }
        });

        thread.start();
    }

    public void onClickBtn(View view){
        Random rand = new Random();
        sendPost(current_location, rand.nextInt(999999), 1);
        Toast.makeText(this, "Sending Accident Location "+current_location, Toast.LENGTH_LONG).show();

    }


}
