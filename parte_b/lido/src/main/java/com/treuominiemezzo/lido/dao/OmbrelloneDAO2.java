package com.treuominiemezzo.lido.dao;

import com.treuominiemezzo.lido.model.Ombrellone;
import com.treuominiemezzo.lido.util.Database;

import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class OmbrelloneDAO2 {

  private static final String QUERY = "SELECT " +
      "    o.id AS id_ombrellone, " +
      "    o.settore, " +
      "    o.numFila AS numero_fila, " +
      "    o.numPostoFila AS numero_ordine, " +
      "    t.nome AS tipologia_nome, " +
      "    CASE " +
      "        WHEN EXISTS ( " +
      "            SELECT 1 " +
      "            FROM OmbrelloneVenduto ov " +
      "            WHERE ov.idOmbrellone = o.id " +
      "            AND ov.data BETWEEN ? AND ? " +
      "            AND ov.contratto != ? " +
      "        ) THEN 1 " +
      "        ELSE 0 " +
      "    END AS occupato " +
      "FROM Ombrellone o " +
      "JOIN Tipologia t ON o.tipologia = t.codice";

  public List<Ombrellone> findByDateRange(LocalDate inizio, LocalDate fine, int escludiContratto) {
    List<Ombrellone> ombrelloni = new ArrayList<>();

    try (Connection conn = Database.getConnection();
        PreparedStatement stmt = conn.prepareStatement(QUERY)) {

      stmt.setDate(1, Date.valueOf(inizio));
      stmt.setDate(2, Date.valueOf(fine));
      stmt.setInt(3, escludiContratto);

      try (ResultSet rs = stmt.executeQuery()) {
        while (rs.next()) {
          ombrelloni.add(new Ombrellone(
              rs.getInt("id_ombrellone"),
              rs.getInt("settore"),
              rs.getInt("numero_fila"),
              rs.getInt("numero_ordine"),
              rs.getString("tipologia_nome"),
              rs.getInt("occupato")));
        }
      }
    } catch (SQLException e) {
      System.err.println("Error fetching umbrellas: " + e.getMessage());
      e.printStackTrace();
    }

    return ombrelloni;
  }
}