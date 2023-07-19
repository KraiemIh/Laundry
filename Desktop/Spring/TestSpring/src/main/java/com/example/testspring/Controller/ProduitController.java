package com.example.testspring.Controller;


import com.example.testspring.DAO.Produit;
import com.example.testspring.Services.IProduitService;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping
@Slf4j
@CrossOrigin(origins = "http://localhost:4200/")
public class ProduitController {
    @Autowired
    IProduitService produitService;


    @GetMapping("/GetAllProduits")
    public List<Produit> GetAllProduits () {
        return produitService.getAllProduits();
    }

    @GetMapping("/GetProduitById/{id}")
    public Produit GetProduitById (@PathVariable("id") Integer id) {
        return produitService.getProduitById(id);
    }

    @PostMapping("/addProduit")
    public Produit addProduit(@RequestBody Produit produit){
        return produitService.addProduit(produit);
    }

    @PutMapping("/updateProduit")
    public Produit updateProduit(@RequestBody Produit produit){
        return produitService.updateProduit(produit);
    }

    @DeleteMapping("/deleteProduit/{id}")
    public void deleteProduit(@PathVariable("id") Integer id){
        produitService.deleteProduit(id);
    }

    @GetMapping("/rechercheProduit/{nom}")
    public List<Produit> rechercheProduit(@PathVariable("nom") String nom) {
        return produitService.rechercheProduit(nom);
    }
}
