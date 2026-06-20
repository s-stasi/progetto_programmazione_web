package com.treuominiemezzo.lido.dao;

import com.treuominiemezzo.lido.model.Contratto;
import com.treuominiemezzo.lido.util.Database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class ContrattoDAO {

  // Recupera i contratti con filtri di data e paginazione
  public List<Contratto> getContrattiFiltered(String dataDa, String dataA, int limit, int offset) {
    List<Contratto> contratti = new ArrayList<>();
    List<Object> params = new ArrayList<>();
    
    StringBuilder sql = new StringBuilder(
      "SELECT c.numProgr, c.data, c.importo, cl.nome, cl.cognome, " +
      "o.id AS idOmbrellone, t.nome AS tipologia, " +
      "MIN(ov.data) AS inizio, MAX(ov.data) AS fine " +
      "FROM Contratto c " +
      "JOIN Cliente cl ON c.stipulatoDa = cl.codice " +
      "LEFT JOIN OmbrelloneVenduto ov ON c.numProgr = ov.contratto " +
      "LEFT JOIN Ombrellone o ON ov.idOmbrellone = o.id " +
      "LEFT JOIN Tipologia t ON o.tipologia = t.codice " +
      "WHERE 1=1 "
    );

    if (dataDa != null && !dataDa.isEmpty()) {
      sql.append("AND c.data >= ? ");
      params.add(java.sql.Date.valueOf(dataDa));
    }
    if (dataA != null && !dataA.isEmpty()) {
      sql.append("AND c.data <= ? ");
      params.add(java.sql.Date.valueOf(dataA));
    }

    sql.append("GROUP BY c.numProgr, c.data, c.importo, cl.nome, cl.cognome, o.id, t.nome ");
    sql.append("ORDER BY c.data DESC, c.numProgr DESC LIMIT ? OFFSET ?");
    params.add(limit);
    params.add(offset);

    try (Connection conn = Database.getConnection();
         PreparedStatement stmt = conn.prepareStatement(sql.toString())) {

      for (int i = 0; i < params.size(); i++) {
        stmt.setObject(i + 1, params.get(i));
      }

      ResultSet rs = stmt.executeQuery();
      while (rs.next()) {
        Contratto c = new Contratto();
        c.setNumProgr(rs.getInt("numProgr"));
        c.setDataStipula(rs.getDate("data").toLocalDate());
        c.setImporto(rs.getDouble("importo"));
        c.setClienteNomeCognome(rs.getString("nome") + " " + rs.getString("cognome"));
        
        c.setIdOmbrellone(rs.getInt("idOmbrellone"));
        c.setTipologiaOmbrellone(rs.getString("tipologia") != null ? rs.getString("tipologia") : "N/D");
        
        java.sql.Date sqlInizio = rs.getDate("inizio");
        if (sqlInizio != null) c.setInizioPrenotazione(sqlInizio.toLocalDate());
        
        java.sql.Date sqlFine = rs.getDate("fine");
        if (sqlFine != null) c.setFinePrenotazione(sqlFine.toLocalDate());

        contratti.add(c);
      }
    } catch (SQLException e) {
      e.printStackTrace();
    }
    return contratti;
  }

  // Conta i record totali per la paginazione
  public int getTotalContrattiFiltered(String dataDa, String dataA) {
    int total = 0;
    List<Object> params = new ArrayList<>();
    StringBuilder sql = new StringBuilder("SELECT COUNT(DISTINCT c.numProgr) AS total FROM Contratto c WHERE 1=1 ");

    if (dataDa != null && !dataDa.isEmpty()) {
      sql.append("AND c.data >= ? ");
      params.add(java.sql.Date.valueOf(dataDa));
    }
    if (dataA != null && !dataA.isEmpty()) {
      sql.append("AND c.data <= ? ");
      params.add(java.sql.Date.valueOf(dataA));
    }

    try (Connection conn = Database.getConnection();
         PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
         
      for (int i = 0; i < params.size(); i++) {
        stmt.setObject(i + 1, params.get(i));
      }
      
      ResultSet rs = stmt.executeQuery();
      if (rs.next()) {
        total = rs.getInt("total");
      }
    } catch (SQLException e) {
      e.printStackTrace();
    }
    return total;
  }
}