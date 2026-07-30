package com.treuominiemezzo.lido.controller;

import com.treuominiemezzo.lido.dao.PrenotazioneDAO;
import com.treuominiemezzo.lido.model.Prenotazione;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.MultipartConfig;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.Locale;
import java.util.Optional;

@WebServlet("/api/prenotazioni")
@MultipartConfig
public class PrenotazioneApiServlet extends HttpServlet {

  private static final DateTimeFormatter DATE_FORMAT = DateTimeFormatter.ISO_LOCAL_DATE;

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");

    String idOmbrelloneStr = req.getParameter("id_ombrellone");
    String dataInizioStr = req.getParameter("data_inizio");

    if (idOmbrelloneStr == null || dataInizioStr == null) {
      sendJson(resp, "{\"success\":false,\"message\":\"Missing required reservation parameters.\"}");
      return;
    }

    try {
      int idOmbrellone = Integer.parseInt(idOmbrelloneStr);
      LocalDate dataInizio = LocalDate.parse(dataInizioStr);

      PrenotazioneDAO dao = new PrenotazioneDAO();
      Optional<Prenotazione> prenotazione = dao.findByUmbrellaAndDate(idOmbrellone, dataInizio);

      if (prenotazione.isPresent()) {
        sendJson(resp, buildSuccessResponse(prenotazione.get()));
      } else {
        sendJson(resp, "{\"success\":false,\"message\":\"No active contract found for this umbrella on the selected date.\"}");
      }
    } catch (NumberFormatException | DateTimeParseException e) {
      sendJson(resp, "{\"success\":false,\"message\":\"Invalid query parameters.\"}");
    }
  }

  @Override
  protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");

    String idOmbrelloneStr = req.getParameter("id_ombrellone");
    String nome = trimOrNull(req.getParameter("nome"));
    String cognome = trimOrNull(req.getParameter("cognome"));
    String dataNascitaStr = req.getParameter("data_nascita");
    String email = trimOrNull(req.getParameter("email"));
    String telefono = trimOrNull(req.getParameter("cellulare"));
    String indirizzo = trimOrNull(req.getParameter("indirizzo"));
    String dataInizioStr = req.getParameter("data_inizio");
    String dataFineStr = req.getParameter("data_fine");
    String prezzoTotaleStr = req.getParameter("prezzo_totale");

    if (idOmbrelloneStr == null || nome == null || cognome == null
        || dataInizioStr == null || dataFineStr == null || prezzoTotaleStr == null) {
      sendJson(resp, "{\"success\":false,\"message\":\"Missing required reservation fields.\"}");
      return;
    }

    try {
      int idOmbrellone = Integer.parseInt(idOmbrelloneStr);
      LocalDate dataInizio = LocalDate.parse(dataInizioStr);
      LocalDate dataFine = LocalDate.parse(dataFineStr);
      double prezzoTotale = Double.parseDouble(prezzoTotaleStr);
      LocalDate dataNascita = (dataNascitaStr != null && !dataNascitaStr.isBlank())
          ? LocalDate.parse(dataNascitaStr) : null;

      PrenotazioneDAO dao = new PrenotazioneDAO();
      String result = dao.createReservation(
          idOmbrellone, nome, cognome, dataNascita, email, telefono, indirizzo,
          dataInizio, dataFine, prezzoTotale);

      switch (result) {
        case "SUCCESS":
          sendJson(resp, "{\"success\":true,\"message\":\"Reservation saved successfully.\"}");
          break;
        case "DUPLICATE":
          sendJson(resp, "{\"success\":false,\"message\":\"Error: umbrella already booked for the selected dates.\"}");
          break;
        default:
          sendJson(resp, "{\"success\":false,\"message\":\"Database error while saving the reservation.\"}");
      }
    } catch (NumberFormatException | DateTimeParseException e) {
      sendJson(resp, "{\"success\":false,\"message\":\"Invalid form data.\"}");
    }
  }

  @Override
  protected void doDelete(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");

    String idStr = req.getParameter("id");
    if (idStr == null || idStr.isBlank()) {
      sendJson(resp, "{\"success\":false,\"message\":\"Missing contract id.\"}");
      return;
    }

    try {
      int idContratto = Integer.parseInt(idStr);
      PrenotazioneDAO dao = new PrenotazioneDAO();

      if (dao.deleteReservation(idContratto)) {
        sendJson(resp, "{\"success\":true,\"message\":\"Reservation cancelled and umbrella freed.\"}");
      } else {
        sendJson(resp, "{\"success\":false,\"message\":\"Reservation not found or could not be deleted.\"}");
      }
    } catch (NumberFormatException e) {
      sendJson(resp, "{\"success\":false,\"message\":\"Invalid contract id.\"}");
    }
  }

  private String buildSuccessResponse(Prenotazione p) {
    StringBuilder json = new StringBuilder("{\"success\":true,\"data\":{");
    json.append("\"id\":").append(p.getId()).append(",");
    json.append("\"nome\":\"").append(escapeJson(p.getNome())).append("\",");
    json.append("\"cognome\":\"").append(escapeJson(p.getCognome())).append("\",");
    json.append("\"data_nascita\":\"").append(formatDate(p.getDataNascita())).append("\",");
    json.append("\"indirizzo\":\"").append(escapeJson(p.getIndirizzo())).append("\",");
    json.append("\"email\":\"").append(escapeJson(p.getEmail())).append("\",");
    json.append("\"cellulare\":\"").append(escapeJson(p.getCellulare())).append("\",");
    json.append("\"prezzo_totale\":").append(String.format(Locale.US, "%.2f", p.getPrezzoTotale())).append(",");
    json.append("\"data_inizio\":\"").append(formatDate(p.getDataInizio())).append("\",");
    json.append("\"data_fine\":\"").append(formatDate(p.getDataFine())).append("\"");
    json.append("}}");
    return json.toString();
  }

  private String formatDate(LocalDate date) {
    return date != null ? date.format(DATE_FORMAT) : "";
  }

  private String trimOrNull(String value) {
    if (value == null) {
      return null;
    }
    String trimmed = value.trim();
    return trimmed.isEmpty() ? null : trimmed;
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

  private void sendJson(HttpServletResponse resp, String json) throws IOException {
    PrintWriter out = resp.getWriter();
    out.print(json);
    out.flush();
  }
}
