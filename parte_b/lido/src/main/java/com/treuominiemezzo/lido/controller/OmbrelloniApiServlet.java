package com.treuominiemezzo.lido.controller;

import com.treuominiemezzo.lido.dao.OmbrelloneDAO;
import com.treuominiemezzo.lido.model.Ombrellone;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.List;

@WebServlet("/api/ombrelloni")
public class OmbrelloniApiServlet extends HttpServlet {

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");

    LocalDate inizio;
    LocalDate fine;
    int escludiContratto = 0;

    try {
      String inizioStr = req.getParameter("inizio");
      String fineStr = req.getParameter("fine");
      String escludiStr = req.getParameter("escludi_contratto");

      inizio = (inizioStr != null && !inizioStr.isBlank()) ? LocalDate.parse(inizioStr) : LocalDate.now();
      fine = (fineStr != null && !fineStr.isBlank()) ? LocalDate.parse(fineStr) : LocalDate.now();

      if (escludiStr != null && !escludiStr.isBlank()) {
        escludiContratto = Integer.parseInt(escludiStr);
      }
    } catch (DateTimeParseException | NumberFormatException e) {
      sendJsonError(resp, "Invalid query parameters.");
      return;
    }

    OmbrelloneDAO dao = new OmbrelloneDAO();
    List<Ombrellone> ombrelloni = dao.findByDateRange(inizio, fine, escludiContratto);

    PrintWriter out = resp.getWriter();
    out.print(buildJsonArray(ombrelloni));
    out.flush();
  }

  private String buildJsonArray(List<Ombrellone> ombrelloni) {
    StringBuilder json = new StringBuilder("[");
    for (int i = 0; i < ombrelloni.size(); i++) {
      if (i > 0) {
        json.append(',');
      }
      json.append(buildJsonObject(ombrelloni.get(i)));
    }
    json.append(']');
    return json.toString();
  }

  private String buildJsonObject(Ombrellone o) {
    return "{"
        + "\"id_ombrellone\":" + o.getIdOmbrellone() + ","
        + "\"settore\":" + o.getSettore() + ","
        + "\"numero_fila\":" + o.getNumeroFila() + ","
        + "\"numero_ordine\":" + o.getNumeroOrdine() + ","
        + "\"tipologia_nome\":\"" + escapeJson(o.getTipologiaNome()) + "\","
        + "\"occupato\":" + o.getOccupato()
        + "}";
  }

  private String escapeJson(String value) {
    if (value == null) {
      return "";
    }
    return value
        .replace("\\", "\\\\")
        .replace("\"", "\\\"")
        .replace("\n", "\\n")
        .replace("\r", "\\r")
        .replace("\t", "\\t");
  }

  private void sendJsonError(HttpServletResponse resp, String message) throws IOException {
    resp.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
    PrintWriter out = resp.getWriter();
    out.print("{\"error\":\"" + escapeJson(message) + "\"}");
    out.flush();
  }
}
