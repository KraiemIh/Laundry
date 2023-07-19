/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package chat;

import java.util.Locale;
import java.util.Map;
import javafx.application.Application;
import javafx.stage.Stage;

/**
 *
 * @author user
 */
public class BasicApp extends Application {

    @Override
    public void start(Stage primaryStage) throws Exception {
       FXLocal local = new FXLocal(Locale.ENGLISH);
        local.addLocaleData(Locale.ENGLISH, Map.of("someKey", "someValueEnglish"));
        local.addLocaleData(Locale.FRENCH, Map.of("someKey", "someValueFrench"));
        
        Button button = new Button();
        button.textProperty().bind(local.localizedStringBinding("someKey"));
        button.setOnAction(e -> {
            local.setSelectedLocale(Locale.FRENCH);
        });

        stage.setScene(new Scene(new StackPane(button), 800, 600));
        stage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
    
}
