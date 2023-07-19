package com.example.testspring.Configuration;

import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Contact;
import io.swagger.v3.oas.models.info.Info;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class OpenAPIConfig {
    @Bean
    public OpenAPI springShopOpenAPI() {
        return new OpenAPI().info(infoAPI());
    }

    public Info infoAPI() {
        return new Info().title("SpringDoc-Demo").description("TP Station de sky").contact(contactAPI());
    }

    public Contact contactAPI() {
        Contact contact = new Contact().name("Aziz Lajili").email("aziz.lajili@esprit.tn").url("");
        return contact;
    }
}
