package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.WebContext;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.util.Arrays;
import java.util.List;

@WebServlet("/tariffe")
public class TariffeServlet2 extends HttpServlet {

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("text/html;charset=UTF-8");
    
    WebContext context = new WebContext(req, resp, getServletContext(), req.getLocale());

    // Passiamo le tipologie alla View esattamente come facevi in PHP
    List<String> tipologie = Arrays.asList("Base", "VIP", "Gazebo", "Disabile");
    context.setVariable("tipologie", tipologie);

    TemplateEngine engine = ThymeleafConfig2.getTemplateEngine(getServletContext());

    try {
      engine.process("tariffe", context, resp.getWriter());
    } catch (Exception e) {
      System.err.println("!!! THYMELEAF RENDERING ERROR !!!");
      e.printStackTrace();
    }
  }
}