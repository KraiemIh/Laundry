package com.example.testspring.Services;


import com.example.testspring.DAO.Produit;
import com.example.testspring.DAO.ProduitRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;
import java.util.stream.Collectors;

@Service
public class ProduitServiceImpl implements IProduitService {
    @Autowired
    ProduitRepository produitRepository;

    @Override
    public Produit addProduit(Produit produit) {
        return produitRepository.save(produit);
    }

    @Override
    public Produit updateProduit(Produit produit) {
        return produitRepository.save(produit);
    }

    @Override
    public void deleteProduit(Integer idproduit) {
        produitRepository.deleteById(idproduit);
    }

    @Override
    public List<Produit> getAllProduits() {
        return produitRepository.findAll();
    }

    @Override
    public Produit getProduitById(Integer idproduit) {
        return produitRepository.findById(idproduit).get();
    }

    @Override
    public List<Produit> rechercheProduit(String nom) {
        return produitRepository.findAll().stream()
                .filter(p -> p.getNom().contains(nom))
                .collect(Collectors.toList());
    }
}
