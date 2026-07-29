package com.treuominiemezzo.lido.controller;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.time.temporal.ChronoUnit;

// This API endpoint handles price calculations for the rates dashboard
@WebServlet("/api/tariffe")
public class TariffeApiServlet extends HttpServlet {

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");

    String tipo = req.getParameter("tipo");
    String dataInizioStr = req.getParameter("inizio");
    String dataFineStr = req.getParameter("fine");

    if (tipo == null || dataInizioStr == null || dataFineStr == null) {
      sendJsonError(resp, "Parametri mancanti.");
      return;
    }

    try {
      LocalDate dataInizio = LocalDate.parse(dataInizioStr);
      LocalDate dataFine = LocalDate.parse(dataFineStr);

      // Validate dates (end date cannot be before start date)
      if (dataFine.isBefore(dataInizio)) {
        sendJsonError(resp, "La data di fine precede la data di inizio.");
        return;
      }

      // Calculate total days (inclusive)
      long giorni = ChronoUnit.DAYS.between(dataInizio, dataFine) + 1;

      // 1. Define base daily prices (you can fetch these from your DB instead)
      double prezzoLordoUnitario = getBasePriceForType(tipo);
      
      // 2. Calculate the base total
      double totaleLordo = prezzoLordoUnitario * giorni;
      
      // 3. Apply business logic for discounts (e.g., > 7 days = 10% off, > 14 days = 20% off)
      double scontoPercentuale = 0.0;
      String etichettaSconto = "Nessuno";

      if (giorni >= 14) {
        scontoPercentuale = 0.20;
        etichettaSconto = "20% (Lungo periodo)";
      } else if (giorni >= 7) {
        scontoPercentuale = 0.10;
        etichettaSconto = "10% (Settimanale)";
      }

      // 4. Calculate final discounted total
      double totaleScontato = totaleLordo - (totaleLordo * scontoPercentuale);

      // Build and send the JSON response expected by rates.js
      PrintWriter out = resp.getWriter();
      String jsonResponse = String.format(
        java.util.Locale.US,
        "{\"success\": true, \"totale\": %.2f, \"prezzo_lordo_unitario\": %.2f, \"giorni\": %d, \"sconto\": \"%s\"}",
        totaleScontato, prezzoLordoUnitario, giorni, etichettaSconto
      );
      
      out.print(jsonResponse);
      out.flush();

    } catch (DateTimeParseException e) {
      sendJsonError(resp, "Formato data non valido.");
    }
  }

  // Helper method to simulate fetching base prices
  private double getBasePriceForType(String tipo) {
    switch (tipo.toLowerCase()) {
      case "vip":
        return 45.00;
      case "gazebo":
        return 60.00;
      case "disabile":
        return 15.00;
      case "base":
      default:
        return 25.00;
    }
  }

  // Helper method to send JSON errors
  private void sendJsonError(HttpServletResponse resp, String message) throws IOException {
    PrintWriter out = resp.getWriter();
    // Escape quotes just to be safe
    String safeMessage = message.replace("\"", "\\\"");
    out.print("{\"success\": false, \"message\": \"" + safeMessage + "\"}");
    out.flush();
  }
}