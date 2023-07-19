package com.example.testspring.Services;

import com.example.testspring.DAO.Produit;

import java.util.List;

public interface IProduitService {

    Produit addProduit(Produit produit);
    Produit updateProduit(Produit produit);
    void deleteProduit(Integer idproduit);
    List<Produit> getAllProduits();
    Produit getProduitById(Integer idproduit);

    List<Produit> rechercheProduit(String nom);

}
