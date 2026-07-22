package com.treuominiemezzo.lido.controller;

import com.treuominiemezzo.lido.dao.ClienteDAO;
import com.treuominiemezzo.lido.model.Cliente;
import com.treuominiemezzo.lido.dao.ContrattoDAO;
import com.treuominiemezzo.lido.model.Contratto;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.List;

// Endpoint for the JavaScript fetch API
@WebServlet("/api/clienti")
@MultipartConfig
public class ClienteApiServlet extends HttpServlet {

  @Override
  protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    String action = req.getParameter("action");
    
    if ("add".equals(action)) {
      handleAdd(req, resp);
    } else if ("update".equals(action)) {
      handleUpdate(req, resp);
    } else if ("delete".equals(action)) {
      handleDelete(req, resp);
    } else {
      sendJsonResponse(resp, false, "Invalid action requested.");
    }
  }

  // Handle client creation
  private void handleAdd(HttpServletRequest req, HttpServletResponse resp) throws IOException {
    String nome = req.getParameter("nome");
    String cognome = req.getParameter("cognome");
    String dataNascitaStr = req.getParameter("data_nascita");
    String email = req.getParameter("email");
    String telefono = req.getParameter("cellulare");

    if (nome == null || nome.trim().isEmpty() || cognome == null || cognome.trim().isEmpty()) {
      sendJsonResponse(resp, false, "Nome e Cognome sono obbligatori.");
      return;
    }

    LocalDate dataNascita = parseDate(dataNascitaStr);
    
    // We pass 0 for ID and numContratti since this is a new client
    Cliente c = new Cliente(0, nome, cognome, dataNascita, email, telefono, "", 0);
    ClienteDAO dao = new ClienteDAO();
    
    if (dao.insertCliente(c)) {
      sendJsonResponse(resp, true, "Cliente registrato con successo!");
    } else {
      sendJsonResponse(resp, false, "Errore durante il salvataggio.");
    }
  }

  // Handle client update
  private void handleUpdate(HttpServletRequest req, HttpServletResponse resp) throws IOException {
    String idStr = req.getParameter("clientId");
    String nome = req.getParameter("nome");
    String cognome = req.getParameter("cognome");
    String dataNascitaStr = req.getParameter("data_nascita");
    String email = req.getParameter("email");
    String telefono = req.getParameter("cellulare");

    if (idStr == null || nome == null || cognome == null) {
      sendJsonResponse(resp, false, "ID, Nome e Cognome sono obbligatori.");
      return;
    }

    try {
      int id = Integer.parseInt(idStr);
      LocalDate dataNascita = parseDate(dataNascitaStr);
      
      Cliente c = new Cliente(id, nome, cognome, dataNascita, email, telefono, "", 0);
      ClienteDAO dao = new ClienteDAO();
      
      if (dao.updateCliente(c)) {
        sendJsonResponse(resp, true, "Cliente aggiornato con successo!");
      } else {
        sendJsonResponse(resp, false, "Errore durante l'aggiornamento.");
      }
    } catch (NumberFormatException e) {
      sendJsonResponse(resp, false, "ID cliente non valido.");
    }
  }

  // Handle client deletion
  private void handleDelete(HttpServletRequest req, HttpServletResponse resp) throws IOException {
    String idStr = req.getParameter("id");
    if (idStr == null) {
      sendJsonResponse(resp, false, "ID cliente mancante.");
      return;
    }

    try {
      int id = Integer.parseInt(idStr);
      ClienteDAO dao = new ClienteDAO();
      String result = dao.deleteCliente(id);
      
      switch (result) {
        case "SUCCESS":
          sendJsonResponse(resp, true, "Cliente eliminato con successo.");
          break;
        case "HAS_CONTRACTS":
          sendJsonResponse(resp, false, "Errore di integrità: Questo cliente ha dei contratti attivi e non può essere eliminato.");
          break;
        case "NOT_FOUND":
          sendJsonResponse(resp, false, "Cliente non trovato.");
          break;
        default:
          sendJsonResponse(resp, false, "Errore del database durante l'eliminazione.");
      }
    } catch (NumberFormatException e) {
      sendJsonResponse(resp, false, "ID cliente non valido.");
    }
  }

  // Helper to parse dates safely
  private LocalDate parseDate(String dateStr) {
    if (dateStr != null && !dateStr.trim().isEmpty()) {
      try {
        return LocalDate.parse(dateStr);
      } catch (DateTimeParseException ignored) {}
    }
    return null;
  }

  // Helper to manually build and send JSON response
  private void sendJsonResponse(HttpServletResponse resp, boolean success, String message) throws IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");
    PrintWriter out = resp.getWriter();
    // Escape quotes to prevent malformed JSON
    String safeMessage = message.replace("\"", "\\\"");
    out.print("{\"success\": " + success + ", \"message\": \"" + safeMessage + "\"}");
    out.flush();
  }

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    String action = req.getParameter("action");
    
    if ("get_contracts".equals(action)) {
      handleGetContracts(req, resp);
    } else {
      sendJsonResponse(resp, false, "Azione GET non valida.");
    }
  }

  // Costruisce il JSON con lo storico dei contratti
  private void handleGetContracts(HttpServletRequest req, HttpServletResponse resp) throws IOException {
    String idStr = req.getParameter("id");
    if (idStr == null) {
      sendJsonResponse(resp, false, "ID cliente mancante.");
      return;
    }

    try {
      int idCliente = Integer.parseInt(idStr);
      ContrattoDAO dao = new ContrattoDAO();
      List<Contratto> contratti = dao.getContrattiByCliente(idCliente);

      // Costruzione manuale del JSON atteso dal frontend
      StringBuilder json = new StringBuilder();
      json.append("{\"success\": true, \"contracts\": [");
      for (int i = 0; i < contratti.size(); i++) {
        Contratto c = contratti.get(i);
        json.append("{")
            .append("\"numProgr\":").append(c.getNumProgr()).append(",")
            .append("\"data\":\"").append(c.getDataStipula().toString()).append("\",")
            .append("\"importo\":").append(c.getImporto())
            .append("}");
        if (i < contratti.size() - 1) json.append(",");
      }
      json.append("]}");

      resp.setContentType("application/json");
      resp.setCharacterEncoding("UTF-8");
      resp.getWriter().print(json.toString());
      resp.getWriter().flush();

    } catch (NumberFormatException e) {
      sendJsonResponse(resp, false, "ID cliente non valido.");
    }
  }
}