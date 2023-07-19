/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package GUI;

import java.io.IOException;
import java.net.URL;
import java.util.ResourceBundle;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.control.Button;
import javafx.scene.control.TextField;

/**
 * FXML Controller class
 *
 * @author user
 */
public class AjouterPersonneController implements Initializable {

    @FXML
    private TextField txtnom;
    @FXML
    private TextField txtprenom;
    @FXML
    private Button btn1;

    /**
     * Initializes the controller class.
     */
    @Override
    public void initialize(URL url, ResourceBundle rb) {
      
        
    }    

    @FXML
    private void AddPerson(ActionEvent event) {
        String nom=txtnom.getText();
        String prenom=txtprenom.getText();
        //taamel l'instance mtaa l personne
        //tammel l'instance mtaa l personne service 
        /// taayet lel méthode eli hachtek biha
        FXMLLoader loader=new FXMLLoader(getClass().getResource("AfficherPersonnes.FXML"));
        try {
            Parent  root =loader.load();
            AfficherPersonneController ac=loader.getController();
                    // njib l controller mtaa l'afiicher persoone
        } catch (IOException ex) {
            Logger.getLogger(AjouterPersonneController.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
}
