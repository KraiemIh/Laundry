/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package chat;

import java.io.*;
import java.net.*;

/**
 *
 * @author user
 */
public class Client {
       public static void main(String[] args) throws IOException{
          try {
            Socket socClient = new Socket("localhost", 5001);
            InputStream is = socClient.getInputStream();
            BufferedReader br = new BufferedReader(new InputStreamReader(is));
            String receivedData = br.readLine();
            System.out.println("Received Data: " + receivedData);
        } catch (IOException e) {
            e.printStackTrace();
        }

       }
}
